<?php

/**
 * 4sq POI api调用oauth token池
 * @package Better_Service_4sq
 * @author sunhy
 *
 */
class Better_Service_4sq_Token
{

	//oauth token
	private static $_token = array(
  	'JX0QFYYSDIFSZT5JOUASAJLZXMVJUWCVEJ2RWOTFLQK0LDIK',
	  'QAKVDJKQ4S1ZUK0QT00CXVVON2K31JPBPGBEUAB0QGTIRZFX',
	);

	// 缓存key
	const CK_4SQ_3RDPOOL = '4sq_3rdpool';
	const PROTOCOL = '4sq.com';

	/**
	 * 返回一个token(better_3rdpool记录), pool中使用轮用机制
	 */
	public static function get()
	{
		$cache = Better_Cache::remote();
		$tokens = $cache->get(self::CK_4SQ_3RDPOOL);
		if (!$tokens || count($tokens) <= 0) $tokens = self::_load();
		if (!$tokens || count($tokens) <= 0) {
		  if (APPLICATION_ENV == 'production') {
		    return null;
		  } else {
  		  $idx = array_rand(self::$_token);
  		  return array('oauth_token' => self::$_token[$idx]);
		  }
		}
		$token = array_shift($tokens);
		$token['#count']++;
		$tokens[$token['#key']] = $token; // $token重新进入$tokens尾部
		$cache->set(self::CK_4SQ_3RDPOOL, $tokens);
		return $token;
	}

	public static function evict($token)
	{
	  $cache = Better_Cache::remote();
	  $tokens = $cache->get(self::CK_4SQ_3RDPOOL);
	  $key = $token['#key'];
	  unset($tokens[$key]); // 从缓存中移除
	  $token['dateline'] = time();
	  $token['total'] += $token['#count'];
	  Better_DAO_Thirdpool::evict($token); // 更新token的dateline和total
	  if (count($tokens) <= 0) $tokens = self::_load();
	  $cache->set(self::CK_4SQ_3RDPOOL, $tokens);
	}

	private static function _load()
	{
	  $fsq = Better_Config::getAppConfig()->service->fsq;
		$limit = (int) $fsq->pool_size; // token池的最大容量
		$params = array(
			'protocol' => self::PROTOCOL,
			'limit' => $limit,
		);
		$rows = Better_DAO_Thirdpool::usable($params);
		$result = array();
		foreach ($rows as &$row) {
		  $key = $row['uid'] . '@' . $row['protocol'];
		  $row['#key'] = $key;
		  $row['#count'] = 0;
		  $result[$key] = $row;
		}
		return $result;
	}

}