<?php

/**
 * 记录log
 *
 * @package Better
 * @author  fengjun <fengj@peptalk.cn>
 *
 */
class Better_Tracelog
{

	private static $instance = null;
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new Better_Tracelog();
		}

		return self::$instance;
	}
	
	public function tracelog($data)
	{
		$userInfo = Better_User::getInstance($data['uid'])->getUser();
		
		$s = array();
		$s['uid'] = $data['uid'];
		$s['itemid'] = $data['itemid'];
		$s['postdate'] = time();
		$s['userip'] = Better_Functions::getIP();
		$s['action'] = $data['action'];
		$s['admin_uid'] =  Better_Registry::get('sess')->admin_uid ? Better_Registry::get('sess')->admin_uid : 0 ;
		$s['data'] = $data['data'];

		//记录用户创建的动作
		$id = Better_DAO_Tracelog::getInstance()->insert($s);

		return true;
	}
}