<?php

/**
 * 用户的关注的人
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_Publictimeline extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_publictimeline';
    	$this->priKey = 'uid';
    	$this->orderKey = 'dateline';
    	
		parent::__construct ($identifier);
		
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	public function reconnection()
	{
		try {
			$this->wdb->getConnection();
			$this->rdb->getConnection();
		} catch (Exception $e) {
			$this->wdb->closeConnection();
			$this->rdb->closeConnection();
			
			$this->assignUserDbConnection(true);
		}
		
		return self::$instance[$this->identifier];
	}
	
	public function somebodyInMyListCount($uid)
	{
		$sql = "SELECT COUNT(*) AS total
		FROM `".$this->tbl."`
		WHERE uid='".$this->identifier."' AND bid LIKE '".$uid.".%'";
		$rs = self::squery($sql, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}
	
	public function &getMine(array $params=array())
	{
		$page = $params['page'] ? (int)$params['page'] : 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$withoutKai = isset($params['without_kai']) ? (bool)$params['without_kai'] : false;
		
		$count = $page*$pageSize;

		$sql = "SELECT bid, dateline
		FROM `".$this->tbl."`
		WHERE uid='".$this->identifier."'
		ORDER BY dateline DESC
		LIMIT ".$count."
		";
		$rs = self::squery($sql, $this->rdb);
		$rows = $rs->fetchAll();		
						
		if (!$withoutKai) {
			$cacher = Better_Cache::remote();
			$data = $cacher->get('kai_pt') ? $cacher->get('kai_pt'): array();
		} else {
			$data = array();
		}
				
		foreach ($rows as $row) {
			$dateline = $row['dateline'];
			$bid = $row['bid'];
				
			$data[$dateline.'.'.$bid] = $bid;
		}
		
		krsort($data);
		$tmp = array_chunk($data, $pageSize);
		$result = $tmp[$page-1];
		
		return $result;
	}
	
	public static function create($bid, $uids)
	{
		$now = time();
		
		if (count($uids)) {
			$sids = Better_DAO_User_Assign::getInstance()->splitUidsToSids($uids);
			Better_Log::getInstance()->logInfo(count($uids), 'debug', true);
			foreach ($sids as $_sid=>$_uids) {
				if (count($_uids)>1000) {
					$tmp = array_chunk($_uids, 1000);
					Better_Log::getInstance()->logInfo(count($tmp), 'debug', true);
					
					foreach ($tmp as $group) {
						$sts = array();
						$sql = "INSERT INTO `".BETTER_DB_TBL_PREFIX."user_publictimeline` VALUES ";						
						foreach ($group as $row) {
							$sts[] = "('".$row."', '".$bid."', '".$now."')";
						}
						$sql .= implode(",", $sts);
						Better_Log::getInstance()->logInfo($sql, 'debug', true);
						
						$cs = parent::assignDbConnection('user_server_'.$_sid);
						$wdb = &$cs['w'];
						self::squery($sql, $wdb);
					}
				} else {
					$sts = array();
					$sql = "INSERT INTO `".BETTER_DB_TBL_PREFIX."user_publictimeline` VALUES ";
					foreach ($_uids as $group) {
						$sts[] = "('".$group."', '".$bid."', '".$now."')";
					}				
	
					$sql .= implode(",", $sts);
					
					$cs = parent::assignDbConnection('user_server_'.$_sid);
					$wdb = &$cs['w'];
					self::squery($sql, $wdb);				
				}
			}
		}
	}
	
	public static function clean($bid)
	{
		$sql = "DELETE FROM `".BETTER_DB_TBL_PREFIX."user_publictimeline` WHERE `bid`='".$bid."' ";
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$wdb = &$cs['w'];
			
			self::squery($sql, $wdb);
		}
	}
	
	public function cleanUid($uid)
	{
		$sql = "DELETE FROM `".BETTER_DB_TBL_PREFIX."user_publictimeline` WHERE `bid` LIKE '".$uid.".%'";
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$wdb = &$cs['w'];
			
			self::squery($sql, $wdb);
		}
	}
	
	/*public function cleanUnfollow($followingUid)
	{
		$sql = "DELETE FROM `".BETTER_DB_TBL_PREFIX."user_publictimeline` WHERE `bid` LIKE '".$followingUid.".%' AND `uid`='".((int)$this->identifier)."';";
		$result = self::squery($sql, $this->wdb);
		
		return $result;
	}*/
	
	
	public function cleanUnfriend($friendUid)
	{
		$sql = "DELETE FROM `".BETTER_DB_TBL_PREFIX."user_publictimeline` WHERE `bid` LIKE '".$friendUid.".%' AND `uid`='".((int)$this->identifier)."';";
		$result = self::squery($sql, $this->wdb);
		
		return $result;
	}
	
	public function summary()
	{
		$sql = "SELECT MAX(dateline) AS max, MIN(dateline) AS min, COUNT(*) AS total FROM `" . BETTER_DB_TBL_PREFIX . "user_publictimeline` WHERE uid='{$this->identifier}'";
		
		$rs = self::query($sql, $this->rdb);
		$row = $rs->fetch();
		
		return array(
			'max' => (int)$row['max'],
			'min' => (int)$row['min'],
			'total' => (int)$row['total']
			);
	}

	/*public static function getFollowingsBidsByCount($uid, $count=300)
	{
		$rows = Better_DAO_User_Status::getInstance($uid)->tinyWebFollowings(array(
			'page' => 1,
			'page_size' => $count
			));
		$bids = array();
		
		foreach ($rows as $row) {
			$bids[$row['bid']] = $row['dateline'];
		}
		
		return $bids;
	}*/
	
	
	public static function getFriendsBidsByCount($uid, $count=300)
	{
		$rows = Better_DAO_User_Status::getInstance($uid)->tinyWebFollowings(array(
			'page' => 1,
			'page_size' => $count
			));
		$bids = array();
		
		foreach ($rows as $row) {
			$bids[$row['bid']] = $row['dateline'];
		}
		
		return $bids;
	}
	
	/*public static function getFollowingsBidsByCountWithDateline($uid, $dateline, $count=300)
	{
		$rows = Better_DAO_User_Status::getInstance($uid)->tinyWebFollowings(array(
			'page' => 1,
			'page_size' => $count,
			'dateline' => $dateline
			));
		$bids = array();
		
		foreach ($rows as $row) {
			$bids[$row['bid']] = $row['dateline'];
		}
		
		return $bids;		
	}*/
	
	public static function getSomebodyBidsByCount($uid, $count=300, $isFriend=false)
	{
		$bids = array();
		
		$count = (int)$count;
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$sql = "SELECT `bid`,`dateline` FROM `".BETTER_DB_TBL_PREFIX."blog` WHERE uid='".$uid."' ";
		if ($isFriend) {
			$sql .= " AND priv!='private' ";
		} else {
			$sql .= " AND priv='public' ";
		}
		$sql .= " ORDER BY `dateline` DESC LIMIT ".$count;
		
		$rs = self::query($sql, $rdb);
		$rows = $rs->fetchAll();
		
		foreach ($rows as $row) {
			$bids[$row['bid']] = $row['dateline'];
		}
		
		return $bids;
	}
	
	public static function getSomebodyBidsByCountWithDateline($uid, $dateline, $count=300, $isFriend=false)
	{
		$bids = array();
		
		$count = (int)$count;
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$sql = "SELECT `bid`, `dateline` FROM `".BETTER_DB_TBL_PREFIX."blog` WHERE uid='".$uid."' AND dateline>".($dateline)." ";
		if ($isFriend) {
			$sql .= " AND priv!='private' ";	
		} else {
			$sql .= " AND priv='public' ";
		}
		$sql .= " ORDER BY `dateline` DESC LIMIT ".$count;

		$rs = self::query($sql, $rdb);
		$rows = $rs->fetchAll();
		
		foreach ($rows as $row) {
			$bids[$row['bid']] = $row['dateline'];
		}
		
		return $bids;
	}	
	
	/**
	 * 执行Replace的SQL操作
	 * 
	 * @param $data
	 * @param $tbl
	 * @return unknown_type
	 */
	public function replace($data, $tbl='')
	{
		$tbl =='' && $tbl = $this->tbl;
		
		$keys = '`'.implode('`,`', array_keys($data)).'`';
		$values = ':'.implode(',:', array_keys($data));
		
		$sql = "REPLACE INTO `{$tbl}` ({$keys})
		VALUES ({$values})";
		
		try {
			$this->reconnection();
			$result = $this->query($sql, $this->wdb, $data);
			
			//删除多余的消息
			$data['uid'] && $this->delPublic($data['uid']);
		} catch(Exception $e) {
			Better_Log::getInstance()->logEmerg('Data:['.serialize($data)."\n".'SQL_REPLACE_ERROR:['.$e->getMessage().']'.' Message:['.$e->getTraceAsString().']', 'db_error');
		}

		return $this->wdb->lastInsertId() ? $this->wdb->lastInsertId() : $result;
	}	
	
	/**
	 * 得到总数
	 */
	public function getCount($uid)
	{
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$tbl = $this->tbl;
		$sql = "SELECT count(*) cnt FROM $tbl WHERE uid='$uid'";
		$rs = self::query($sql, $rdb);
		$row = $rs->fetch();

		return $row['cnt'];
	}
	
	/**
	 * 删除过期的数据
	 */
	public  function delPublic($uid)
	{
		$rnd = rand(1, 3);
		if ($rnd != 1 ) return -1;
		
		$count = $this->getCount($uid);
		if ($count <= 300 ) return 0;
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$tbl = $this->tbl;
		$sql = "SELECT min(dateline) md FROM  (SELECT dateline FROM $tbl WHERE uid='$uid' ORDER BY dateline DESC LIMIT 301) AS a";		
		$rs = self::query($sql, $rdb);
		$row = $rs->fetch();
		$min_d = $row['md'];
	
		$sql = "DELETE FROM $tbl WHERE uid='$uid' AND dateline<=$min_d";
		$rs = self::query($sql, $rdb);
		
		return 1;
	}
}