<?php

/**
 * 微博客相关数据操作
 *
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Todo extends Better_DAO_Base
{
	
	private static $instance = array();

	
    public function __construct($identifier = 0)
    {
		$this->tbl = BETTER_DB_TBL_PREFIX.'todo';
		$this->priKey = 'bid';
		$this->orderKey = 'dateline';
		
		parent::__construct ($identifier);
		$this->assignUserDbConnection();
	}
	
  public static function getInstance($identifier = 0)
	{
		if (!isset(self::$instance[$identifier]) || is_null(self::$instance[$identifier])) {
			self::$instance[$identifier] = new Better_DAO_Todo($identifier);
		}
		return self::$instance[$identifier];
	}

	public function getBidByPoi($uid,$poiid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, 'bid');
		$select->where("uid=?",$uid);
		$select->where("poi_id=?",$poiid);
		$result = self::squery($select, $this->rdb);
		$return=$result->fetch();
		return $return['bid'];
	}

	public static function count($poiId, $params=array())
	{
		// 参数'uid': 返回我、好友、其他人想来此poi的人数
		$uid = is_array($params) && array_key_exists('uid', $params) ? (int)$params['uid'] : 0;
		$result = array(
			'me' => 0,
			'friend' => 0,
			'other' => 0,
			'total' => 0,
		);

		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$sql = "SELECT COUNT(distinct b.uid) ucount";
			if ($uid) $sql .= ", IF(b.uid=$uid,1,0) is_me, IF(bf.uid,1,0) is_friend";
			$sql .= " FROM `".BETTER_DB_TBL_PREFIX."todo` AS b
				INNER JOIN `".BETTER_DB_TBL_PREFIX."account` AS a ON a.uid=b.uid
				LEFT JOIN `".BETTER_DB_TBL_PREFIX."user_banned` AS ub ON ub.uid=b.uid";
			if ($uid) $sql .= " LEFT JOIN `".BETTER_DB_TBL_PREFIX."friends` AS bf ON bf.uid=b.uid AND bf.friend_uid=$uid ";
			$sql .= " WHERE ub.uid IS NULL AND b.poi_id='" . $poiId . "'";
			if ($uid) $sql .= " GROUP BY IF(b.uid=$uid,1,0), IF(bf.uid,1,0)";

			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row) {
				if ($uid) {
					if ($row['is_me'] == 1) $result['me'] += $row['ucount'];
					else if ($row['is_friend'] == 1) $result['friend'] += $row['ucount'];
					else $result['other'] += $row['ucount'];
				}
				$result['total'] += $row['ucount'];
			}
		}
		return $result;
	}

	/**
	 * 搜索poi的todo信息
	 * @param $poiId
	 * @param $count
	 * @param $avatar
	 * @param $params
	 */
	public static function users($poiId, $page=1, $count=BETTER_PAGE_SIZE,$avatar=true,$params)
	{
		$return = array(
			'total' => 0,
			'rows' => array()
			);
		// 参数'uid': 按好友, 非好友排序
		$uid = is_array($params) && array_key_exists('uid', $params) ? (int)$params['uid'] : 0;
		$timestart = $params['timestart'] ? $params['timestart'] : 0;
		$timeend = $params['timeend'] ? $params['timeend'] : 0;
 		$limit = $page * $count + 1;
		$results = array();

		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_' . $sid);
			$rdb = &$cs['r'];
			
			$sql = "SELECT b.uid, MAX(b.dateline) AS checkin_time, COUNT(b.uid) as checkin_count, p.username, p.nickname, p.avatar";
			if ($uid) $sql .= ", IF(b.uid=$uid,1,0) is_me, IF(bf.uid,1,0) is_friend";
			$sql .= " FROM `".BETTER_DB_TBL_PREFIX."todo` AS b
				INNER JOIN `".BETTER_DB_TBL_PREFIX."profile` AS p ON p.uid=b.uid 
				LEFT JOIN `".BETTER_DB_TBL_PREFIX."user_banned` AS ub ON ub.uid=b.uid";
			if ($uid) $sql .= " LEFT JOIN `".BETTER_DB_TBL_PREFIX."friends` AS bf ON b.uid=bf.uid AND bf.friend_uid=$uid ";
			$sql .= " WHERE ub.uid IS NULL AND b.poi_id='" . $poiId . "'";
			if ($avatar === true) {
				$sql .= " AND p.avatar!=''";
			}
			if($timeend && $timestart){
				$sql .= " AND b.dateline> $timestart AND b.dateline < $timeend";
			}
			$sql .= "
			GROUP BY b.uid";
			if ($uid) $sql .= " ORDER BY IF(b.uid=$uid,1,0) DESC, IF(bf.uid,1,0) DESC, checkin_time DESC";
			else $sql .= " ORDER BY checkin_time DESC";
			$sql .= " LIMIT ".$limit;

			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				if ($uid) $key = $v['is_me'].$v['is_friend'].$v['checkin_time'].'.'.(10000000-$v['uid']);
				else $key = $v['times'].$v['checkin_time'].'.'.(10000000-$v['uid']);
				$results[$key] = $v;
			}
		}

		//	取出合并后的limit条数据
		if (count($results) > 0) {
			$return['total'] = count($results);
			krsort($results);
			$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
			unset($results);
			$ps = array_chunk($data[0], $count);
			if (isset($ps[$page-1])) {
				$return['rows'] = &$ps[$page-1];
			}
		}
		return $return;
	}

	public function getBids($uid,$poiid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, 'bid');
		$select->where("uid=?",$uid);
		$select->where("poi_id=?",$poiid);
		$result = self::squery($select, $this->rdb);
		return $result->fetchAll();
	}

	public function getMaxBids($poiIds)
	{
		$return = array();
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('poi_id'),
			new Zend_Db_Expr('max(bid) AS bid')
		));
		$select->where('uid=?', $this->identifier);
		if (is_array($poiIds)) {
			if (count($poiIds) > 0) $select->where('poi_id in (?)', $poiIds);
			else $select->where('0');
		} else if (is_int($poiIds)) {
			$select->where('poi_id=?', $poiIds);
		} else {
			$select->where('0');
		}
		$select->group('poi_id');
		$result = self::squery($select, $this->rdb);
		$rows = $result->fetchAll();
		foreach ($rows as $row) {
			$return[$row['poi_id']] = $row['bid'];
		}
		return $return;
	}
}

?>