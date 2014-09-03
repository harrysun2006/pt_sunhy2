<?php

/**
 * 用户分配表
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_User_Assign extends Better_DAO_Base
{
	private static $instance = null;
	private static $serverIds = array(
		'1', '2'
		);
	protected static $sids = array();
	private static $cacheKey = 'uid_to_sid';

	public function __construct($identifier=null)
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'user_assign';
		$this->priKey = 'uid';
		$this->orderKey = &$this->priKey;
		
		parent::__construct($identifier);
		
		$tmp = Better_Cache::remote()->get(self::$cacheKey);
		if (is_array($tmp)) {
			self::$sids = &$tmp;
		}
	}
	
	public static function getInstance($new=false)
	{
		if (self::$instance==null || $new) {
			self::$instance = new self();
			$db = parent::registerDbConnection('assign_server', $new);
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}
	
	/**
	 * 
	 * 根据用户id获取其读数据库
	 * 
	 * @param unknown_type $uid
	 */
	public function getRdbByUid($uid)
	{
		$sid = $this->getServerIdByUid($uid);

		$cs = parent::assignDbConnection('user_server_'.$sid);
		$rdb = &$cs['r'];
		
		return $rdb;
	}
	
	/**
	 * 
	 * 根据用户id获取其读、写数据库
	 * 
	 * @param unknown_type $uid
	 */
	public function getServerIdByUid($uid)
	{
		if (!isset(self::$sids[$uid])) {
			$select = $this->rdb->select();
			$select->from($this->tbl, array(
				'sid'
				));
			$select->where('uid=?', $uid);
			$rs = self::squery($select, $this->rdb);
			$row = $rs->fetch();
			
			self::$sids[$uid] = $row['sid'];
			
			Better_Cache::remote()->set(self::$cacheKey, self::$sids);
		}
		
		return self::$sids[$uid];
	}
	
	/**
	 * 
	 * 根据用户uid的数组，获取这些用户分布在哪些用户片中
	 * 
	 * @param array $uids
	 */
	public function getServerIdsByUids(array $uids)
	{
		$sids = array();
		
		if (count($uids)) {
			$select = $this->rdb->select();
			$select->from($this->tbl, 'DISTINCT(sid) AS sid');
			$select->where('uid IN(?)', $uids);
			
			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();

			foreach($rows as $row) {
				$sids[] = $row['sid'];
			}
		}
		
		return $sids;
	}
	
	/**
	 * 
	 * 根据用户uid的数组，获取这些用户分布在哪些用户片中，并详细告知每个用户的所在分片
	 * @param array $uids
	 */
	public function splitUidsToSids(array $uids)
	{
		$result = array();
		
		if (count($uids)) {
			$select = $this->rdb->select();
			$select->from($this->tbl, 'uid');
			$select->where('uid IN (?)', $uids);
			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();
			
			foreach ($rows as $row) {
				$result[$row['sid']][] = $row['uid'];
			}
		}
		
		return $result;
	}

	/**
	 * 获取所有的用户server
	 *
	 * @return array
	 */
	public function getServerIds()
	{
		if (count(self::$serverIds)==0) {
			$select = $this->rdb->select();
			$select->from($this->tbl, 'DISTINCT(sid) AS sid');

			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row) {
				self::$serverIds[] = $row['sid'];
			}
		
		}
		return self::$serverIds;
	}
}