<?php

/**
 * 应用启动时一些特殊的用户数据操作
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_User_BootStrap extends Better_DAO_Base
{
	protected static $instance = null;
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * 获取所有被锁定的用户
	 * 
	 */
	public function getLockedUids()
	{
		$results = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from($this->profileTbl, 'uid');
			$select->where('state=?', Better_User_State::LOCKED);

			$rs = $this->query($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[] = $v;
			}
		}
		
		return $results;
	}
}