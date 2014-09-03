<?php

/**
 * 客户端收集的用户号码簿
 * 
 * @package Better.Mobile
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Mobile_Contacts
{

	public static $key = '2012isnotthelast';
	protected static $iv = 'fedcba9876543210';
	
	/**
	 * 解密客户端提交的数据
	 * 
	 * @param unknown_type $data
	 * @return string
	 */
	public static function decrpt($data, $regulate=false)
	{
		$results = array();
		$key = Better_Config::getAppConfig()->aes->key;
		$xml = mcrypt_ecb(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_DECRYPT);
		$xml = trim($xml);
		
		if ($xml) {
			try {
				$arr = Better_Json::xml2array($xml);
			} catch (Exception $e) {
				$arr = array();
			}
			
			if (is_array($arr)) {
				$results = &$arr;
			}
		}
		if ($regulate) {
			self::regulate($results);
			$results['xml'] = $xml;
		}
		return $results;
	}
	
	/**
	 * 加密字符串
	 * 
	 * @param $data
	 * @return string
	 */
	public static function enc($data)
	{
		$key = Better_Config::getAppConfig()->aes->key;
		$result = mcrypt_ecb(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_ENCRYPT);
		return $result;
	}
	
	/**
	 * 记录入库
	 * 
	 * @param unknown_type $data
	 */
	public static function log($uid, $data)
	{
		Better_DAO_MobileContacts::getInstance()->insert(array(
			'uid' => $uid,
			'dateline' => time(),
			'inner_id' => $data['id'],
			'name' => $data['name'],
			'category' => $data['category'],
			'content' => $data['content'],
			));
	}

	/**
	 * 格式化地址簿, 输入:
	 * 'users'=>array('user'=>array('id'=>..., 'name'=>...)) 或
	 * 'users'=>array('user'=>array(0=>array('id'=>..., 'name'=>...), 1=>...))
	 * 其中user可能是:
	 * array('id'=>..., 'name'=>..., 'contact'=>array('category'=>..., 'content'=>...))或
	 * array('id'=>..., 'name'=>..., 'contact'=>array(0=>array('category'=>..., 'content'=>...), 1=>...))
	 * 格式化输出:
	 * 'users'=>array(0=>array('id'=>..., 'name'=>..., 'contact'=>array(0=>array('category'=>..., 'content'=>...), 1=>...)), 1=>...)
	 * @param $results
	 */
	protected static function regulate(array &$results)
	{
		if (!isset($results['users'])) $results['users'] = array();
		if (isset($results['users']['user']['id'])) { // 只有一个user节点
			self::regulateUserNode($results['users']['user']);
			$results['users'][] = $results['users']['user'];
		} else if (is_array($results['users']['user']) && count($results['users']['user'])>0) {
			foreach ($results['users']['user'] as $row) {
				self::regulateUserNode($row);
				$results['users'][] = $row;
			}
		}
		if (isset($results['users']['user'])) unset($results['users']['user']);
		return $results;
	}

	protected static function regulateUserNode(&$row)
	{
		if (!isset($row['contact'])) return;
		$pat = '#^(?:\+?86\s*)?([0-9]{11})$#i';
		if (isset($row['contact']) && isset($row['contact']['category'])) { // 只有一个联系方式
			$c = $row['contact'];
			$content = $c['content'];
			if (preg_match($pat, $content, $m)) {
				$c['cell_no'] = '86' . $m[1];
			} else if (Better_Functions::checkEmail($content)) {
				$c['email'] = $content;
			}
			unset($row['contact']);
			$row['contact'][] = $c;
		} else if (count($row['contact'])>0 && isset($row['contact'][0]['content'])) {
			for ($i = 0; $i < count($row['contact']); $i++) {
				$contact = &$row['contact'][$i];
				if (!is_array($contact) || !isset($contact['content'])) continue;
				$content = $contact['content'];
				if (preg_match($pat, $content, $m)) {
					$contact['cell_no'] = '86' . $m[1];
				} else if (Better_Functions::checkEmail($content)) {
					$contact['email'] = $content;
				}
			}
		}
	}

	/**
	 * 分析结果
	 * 
	 * @param array $results
	 */
	public static function parse(array $results, $uid)
	{
		$phones = array();

		if (isset($results['users']['user']['id'])) {
			//	只有一个user节点
			$phones = self::parseUserNode($results['users']['user'], $uid);
		} else {
			if (is_array($results['users']['user']) && count($results['users']['user'])>0) {
				foreach ($results['users']['user'] as $row) {
					$rowResults = self::parseUserNode($row, $uid);
					$phones = array_merge($phones, $rowResults);
				}
			}
		}
	
		return $phones;
	}
	
	/**
	 * 分析一个user节点
	 * 
	 * @param array $row
	 * @return array
	 */
	protected static function parseUserNode($row, $uid)
	{
		$parsed = array();
		
		if (isset($row['contact']) && isset($row['contact']['category'])) {
			if ($row['contact']['content']) {
				$row['contact']['content'] = self::filterPhone($row['contact']['content']);
				$parsed[$row['id']] = $row['contact']['content'];
				
				self::log($uid, array(
					'id' => $row['id'],
					'name' => $row['name'],
					'category' => $row['contact']['category'],
					'content' => $row['contact']['content'],
					));
			}
		} else if (count($row['contact'])>0 && isset($row['contact'][0]['content'])) {
			foreach ($row['contact'] as $contact) {
				if ($contact['content'] && (Better_Functions::checkEmail($contact['content']) || Better_Functions::isCell($contact['content']))) {
					$contact['content'] = self::filterPhone($contact['content']);
					$parsed[$row['id']][] = $contact['content'];
	
					self::log($uid, array(
						'id' => $row['id'],
						'name' => $row['name'],
						'category' => $contact['category'],
						'content' => $contact['content']
						));
				}
			}
		}

		return $parsed;
	}
	
	protected static function filterPhone($phone)
	{
		$phone = preg_replace('/^\+86([0-9]+)$/', '\1', $phone);
		$phone = trim($phone);
		
		return $phone;
	}
}