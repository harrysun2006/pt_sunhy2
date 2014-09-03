<?php
/**
 * 电话地址簿
 * 
 * @package Better
 * @author sunhy <sunhy@peptalk.cn>
 *
 */
class Better_AddressBook
{

	/**
	 * 取用户的地址簿
	 * @param $uid: 用户ID
	 * @return array(
	 *   'data' => 地址簿的主记录
	 *   'items' => 地址簿条目数组
	 * )
	 */
	public static function get($uid)
	{
		$dao = Better_DAO_AddressBook::getInstance();
		return array(
			'data' => $dao->get($uid),
			'items' => $dao->getItems($uid),
		);
	}

	/**
	 * 保存地址簿, 先删除用户原来的地址簿, 再保存新的
	 * @return array(
	 *   'data' => 地址簿的主记录
	 *   'items' => 地址簿条目数组
	 * )
	 * @param array $params array(
	 *   'user' => 用户信息,
	 *   'uid' => 用户标识(可选),
	 *   'cell_no' => 手机号码(可选),
	 *   'content' => 上传的xml地址簿,
	 *   'items' => xml地址簿解析后的条目
	 * )
	 */
	public static function save(array $params)
	{
		$dao = Better_DAO_AddressBook::getInstance();
		$user = $params['user'] ? $params['user'] : null;
		$uid = $params['uid'] ? $params['uid'] : ($user && $user['uid'] ? $user['uid'] : 0);
		$cell_no = $params['cell_no'] ? $params['cell_no'] : ($user && $user['cell_no'] ? $user['cell_no'] : '');
		$content = $params['content'] ? $params['content'] : '';
		$hash = md5($content);
		$_data = array(
			'uid' => $uid,
			'cell_no' => $cell_no,
			'dateline' => time(),
			'hash' => $hash,
			'content' => $content,
		);

		$items = $params['items'] ? $params['items'] : array();
		$_items = array();
		foreach ($items as $item) {
			if (!is_array($item) || !isset($item['id']) || !isset($item['name']) || !isset($item['contact'])) continue;
			$cid = $item['id'];
			$name = $item['name'];
			$contact = $item['contact'];
			if (!is_array($contact) || count($contact) == 0) continue;
			foreach($contact as $c) {
				if (!is_array($c) || !isset($c['category']) || !isset($c['content'])) continue;
				if (key_exists('email', $c)) {
					$category = 'email';
					$content = $c['email'];
				} elseif (key_exists('cell_no', $c)) {
					$category = 'cell_no';
					$content = $c['cell_no'];
				} else {
					$category = 'unknown';
					$content = $c['content'];
				}
				$_items[] = array(
					'id' => 0,
					'uid' => $uid,
					'cid' => $cid,
					'name' => $name,
					'category' => $category,
					'content' => $content,
				);
			}
		}

		$result = array(
			'data' => &$_data,
			'items' => &$_items,
		);
		if (!$uid) return $result;
		$dao->deleteAll($uid);
		$dao->insert($_data);
		foreach ($_items as &$_item) {
			$_item['id'] = $dao->insertItem($_item);
		}
		return $result;
	}

	/**
	 * 在地址簿中查找有此用户信息的其他用户ID(反向查找)
	 * @param unknown_type $user
	 */
	public static function findReversed($user, $page = 1, $count = 50)
	{
		return Better_DAO_AddressBook::getInstance()->findItems($user, $page, $count);
	}
}