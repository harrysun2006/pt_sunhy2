<?php
/**
 * admin页面第三方token池管理
 * @author sunhy
 */

class Better_Admin_Thirdpool
{
	
	/**
	 * 获得tokens
	 */
	public static function getTokens(array $params = array())
	{
		$return = array (
			'count' => 0,
			'rows' => array(),
		);
		$uid = Better_Registry::get('sess')->admin_uid;
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;

		$rows = Better_DAO_Thirdpool::getAllTokens($params);
		$data = array_chunk($rows, $pageSize);
		$return['count'] = count($rows);
		$return['rows'] = $data[$page - 1];
		if (!$return['rows']) $return['rows'] = array();
		unset($data);
		return $return;
	}

	/**
	* 新加入一个token, $uid必须绑定过此协议
	* @param unknown_type $uid
	*/
	public static function addToken($uid, $protocol)
	{
	  $params = array(
		    'uid' => $uid,
		    'protocol' => $protocol,
	  );
	  $bindings = Better_DAO_ThirdBinding::getInstance($uid)->getAll($params);
	  $binding = null;
	  if (count($bindings) > 0) $binding = $bindings[0];
	  if ($binding && $binding['oauth_token']) {
	    $data = array(
		      'uid' => $binding['uid'],
		      'protocol' => $binding['protocol'],
		      'username' => $binding['username'],
		    	'password' => $binding['password'],
	        'oauth_token' => $binding['oauth_token'],
	        'oauth_token_secret' => $binding['oauth_token_secret'],
		    	'dateline' => 0,
		    	'total' => 0,
	    );
	    Better_DAO_Thirdpool::getInstance()->replace($data);
	    return $data;
	  } else {
	    return false;
	  }
	}

	/**
	* 删除一个token
	* @param unknown_type $uid
	*/
	public static function removeToken($uid, $protocol)
	{
	  return Better_DAO_Thirdpool::getInstance()->deleteByCond(array(
	    'uid' => $uid,
	    'protocol' => $protocol,
	  ));
	}
}
?>