<?php

/**
 * 挖宝游戏session
 * 
 * @package Better.DAO.Game
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Game_Session extends Better_DAO_Game_Base
{
	protected static $instance = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'game_session';
		$this->priKey = 'session_id';
		$this->orderKey = &$this->priKey;
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	/**
	 * 
	 * 当前给某人的有效游戏邀请
	 * @param unknown_type $uid
	 */
	public function validInvitesToSomebody($uid)
	{
		$offset = Better_Config::getAppConfig()->game->invite_timeout;
		
		$result = array();
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('coplayer_uid=?', $uid);
		$select->where('expired=?', 0);
		$select->where('start_time=?', 0);
		$select->where('create_time>=?', time()-$offset);
		
		$rs = self::squery($select, $this->rdb);
		$result = $rs->fetchAll();
		
		return $result;
	}
	
	/**
	 * 同步某个用户的Session
	 * 
	 * 
	 */
	public function sync($uid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where($this->rdb->quoteInto('starter_uid=?', $uid).' OR '.$this->rdb->quoteInto('coplayer_uid=?', $uid));
		$select->where('expired!=?', 1);
		$select->order('create_time DESC');
		$select->limit(1);

		$rs = self::squery($select, $this->rdb);

		return $rs->fetch();
	}
	
	/**
	 * 获取超时的邀请
	 * 
	 * @return array
	 */
	public function getTimeoutInvite($game='hunting')
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('start_time=?', 0);
		$select->where('create_time<?', time()-(int)Better_Config::getAppConfig()->game->invite_timeout);
		$select->where('ended=?', 0);
		$select->where('expired=?', 0);
		$select->where('game=?', $game);
		
		$rs = self::squery($select, $this->rdb);
		
		return $rs->fetchAll();
	}
	
	/**
	 * 获取超时的捡宝
	 * 
	 * @return array
	 */
	public function getTimeoutTreasure($game='hunting')
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('start_time>?', 0);
		$select->where('end_time<?', time()-(int)Better_Config::getAppConfig()->game->pickup_timeout);
		$select->where('ended=?', 1);
		$select->where('expired=?', 0);
		$select->where('game=?', $game);
		$select->where($this->rdb->quoteInto('starter_pickup=?', '0').' OR '.$this->rdb->quoteInto('coplayer_pickup=?', '0'));

		$rs = self::squery($select, $this->rdb);
		
		return $rs->fetchAll();		
	}

	/**
	 * 清理过期的Session
	 * 
	 * @return null
	 */
	public function cleanExpired($game='hunting')
	{
		$sql = "UPDATE `".$this->tbl."` SET `expired`='1' ";
		$sql .= " WHERE `expired`='0' AND ((`start_time`='0' AND ".time()."-`create_time`>".(int)Better_Config::getAppConfig()->game->invite_timeout.")";
		$sql .= " OR ";
		$sql .= " (`ended`='1' AND `end_time`!='0' AND ".time()."-`end_time`>".(int)Better_Config::getAppConfig()->game->pickup_timeout."))";
		
		self::squery($sql, $this->wdb);		
	}
	
	/**
	 * 获取正在进行的游戏session
	 * 
	 * @return array
	 */
	public function runningSessions($game='hunting')
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('ended=?', '0');
		$select->where('start_time!=?', '0');
		$select->where('game=?', $game);
		$select->where('expired=?', '0');
		$select->order('start_time ASC');

		$rs = self::squery($select, $this->rdb);
		return $rs->fetchAll();
	}
	
	/**
	 * 是否可以邀请某人
	 * 
	 * @return bool
	 */
	public function isInvited($starterUid, $coplayerUid, $game='hunting')
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total'),
			));
		$select->where('starter_uid=?', $starterUid);
		$select->where('coplayer_uid=?', $coplayerUid);
		$select->where('ended=?', 0);
		$select->where('start_time=?', 0);
		$select->where('create_time>?', time()-(int)Better_Config::getAppConfig()->game->invite_timeout);

		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return $row['total']>0 ? true : false;
	}

	/**
	 * 判断某个用户是否在玩游戏
	 * 
	 * @return bool
	 */
	public function isGaming($uid, $game='hunting')
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$where = "
		(	
			((`expired`=0
			AND 
			`ended`=0 
			AND 
			start_time!=0 
			AND 
			".$this->rdb->quoteInto('game=?', $game)." 
			AND 
			".$this->rdb->quoteInto('start_time>?', time()-(int)Better_Config::getAppConfig()->game->play_timeout)."
			) 
		 	OR 
			(
				`ended`=1 
				AND 
				`expired`=0 
			))
			AND 
			(
				(`starter_pickup`=0 AND `starter_uid`='".$uid."') 
				OR 
				(`coplayer_pickup`=0 AND `coplayer_uid`='".$uid."')
			)			
			AND 
			(".$this->rdb->quoteInto('starter_uid=?', $uid).' OR '.$this->rdb->quoteInto('coplayer_uid=?', $uid).")
		)
		";

		$select->where($where);

		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}
	
	/**
	 * 判断某个用户是否有等待响应的邀请
	 * 
	 * @return
	 */
	public function hasPendingInvite($uid, $game='hunting')
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total'),
			));
		$select->where('ended=?', 0);
		$select->where('start_time=?', 0);
		$select->where('starter_uid=?', $uid);
		$select->where('create_time>?', time()-(int)Better_Config::getAppConfig()->game->invite_timeout);//300
		$select->where('game=?', $game);
		$select->where('expired=?', 0);

		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();

		return (int)$row['total'];
	}
	
	/**
	 * 
	 * 等待确认的邀请
	 * @param unknown_type $uid
	 * @param unknown_type $game
	 */
	public function pendingInvites($uid, $game='hunting')
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('ended=?', 0);
		$select->where('start_time=?', 0);
		$select->where('starter_uid=?', $uid);
		$select->where('create_time>?', time()-(int)Better_Config::getAppConfig()->game->invite_timeout);//300
		$select->where('game=?', $game);
		$select->where('expired=?', 0);

		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();

		return $rows;
	}	
	
	/**
	 * 获取今天不能挖宝的人
	 * 
	 * @return array
	 */
	public function todayCantUids($game='hunting')
	{
		$uids = array();
		return $uids;
		/*
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			'starter_uid', new Zend_Db_Expr('COUNT(session_id) AS total')
			));
		$select->where('start_time>?', time()-3600*24);
		$select->where('game=?', $game);
		$select->group('starter_uid');
		$select->having('total>=?', 3);
		
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		
		foreach ($rows as $row) {
			$uids[] = $row['starter_uid'];
		}
		
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			'coplayer_uid', new Zend_Db_Expr('COUNT(session_id) AS total')
			));
		$select->where('start_time>?', time()-3600*24);
		$select->where('game=?', $game);
		$select->group('coplayer_uid');
		$select->having('total>=?', 5);
		
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		
		foreach ($rows as $row) {
			$uids[] = $row['coplayer_uid'];
		}		
		
		$unlimitedUids = explode('|', Better_Config::getAppConfig()->game->unlimited_uids);
		
		if (Better_Config::getAppConfig()->game->unlimited) {
			$uids = array();
		}
		
		return array_unique($uids);*/
	}
	
}