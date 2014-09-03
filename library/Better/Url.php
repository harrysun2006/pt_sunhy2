<?php

/**
 * 短地址服务
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Url
{
	
	/**
	 * 保存一个url
	 * 
	 * @param string $url
	 * @return integer $id
	 */
	public static function save($url)
	{
		$id = self::exists($url);
		if (!$id) {
			$id = Better_DAO_Url::getInstance()->insert(array(
				'url' => trim($url),
				'dateline' => time(),
				'clicks' => 0,
				));	
		}
		
		return $id;
	}
	
	/**
	 * 替换一个吼吼内容中的url到数据库
	 * 
	 * @param unknown_type $url
	 */
	public static function toDb($url)
	{
		$id = self::save($url);
		$url = BETTER_BASE_URL.'/u/'.Better_Alpha::getInstance()->C($id);
		
		return $url;
	}
	
	/**
	 * 判断一个url是否存在
	 * 
	 * @param url $url
	 * @return integer
	 */
	public static function exists($url)
	{
		$row = Better_DAO_Url::getInstance()->get(array(
			'url' => trim($url)
			));
		return isset($row['id']) ? $row['id'] : 0;
	}
	
	/**
	 * 根据id获取一个url
	 * 
	 * @param string $id
	 * @return string
	 */
	public static function parse($id)
	{
		$url = BETTER_BASE_URL;
		
		if (strlen($id)) {
			$id = Better_Alpha::getInstance()->R($id);
			if ($id) {
				$row = Better_DAO_Url::getInstance()->get($id);
				if ($row['url']) {
					$url = $row['url'];
					
					Better_DAO_Url::getInstance()->updateByCond(array(
						'clicks' => $row['clicks']+1
					), array(
						'id' => $id
						));
				}
			}
		}
		
		return $url;
	}
}