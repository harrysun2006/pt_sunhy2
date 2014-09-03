<?php

/**
 * 取勋章
 * 
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge extends Better_DAO_Base
{
	private static $instance = null;

	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'badge';
		$this->priKey = 'id';
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
	
	public function getBadge($bid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS b', array('*', 'b.total as count'));
		$select->joinleft(BETTER_DB_TBL_PREFIX.'badge_exchange AS e', 'b.id=e.badge_id', array(
			'e.expire_at', 'e.total', 'e.code', 'e.success_tips', 'e.en_success_tips', 'e.help_tips', 'e.en_help_tips', 'e.remain'
			));
		$select->where('b.id=?', $bid);
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return isset($row['id']) ? $row : array();
	}
	
	public function getAllAvailable(array $params=array())
	{
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS b');
		$select->joinleft(BETTER_DB_TBL_PREFIX.'badge_exchange AS e', 'b.id=e.badge_id', array(
			'e.expire_at', 'e.total', 'e.code', 'e.success_tips', 'e.en_success_tips', 'e.help_tips', 'e.en_help_tips', 'e.remain'
			));		
		$select->where('`b`.`active`=?', 1);
		$select->order('b.id DESC');
		
		if (is_array($params) && count($params)) {
			foreach ($params as $k=>$v) {
				is_array($v) ? $select->where($k.' IN (?)', $v) : $select->where($k.'=?', $v);
			}
		}
		
		$rs = self::squery($select, $this->rdb);
		
		return $rs->fetchAll();
	}
	
	public static function getBadgeUsers($bid)
	{
		$page = 1;
		$pageSize = 110;
		$order = 'get_time';
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();

		$results = array();
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];

			$sql = <<<EOT
SELECT `p`.`uid`, `p`.`username`, `p`.`nickname`, `p`.`gender`, `p`.`birthday`, `p`.`self_intro`, `p`.`language`, `p`.`avatar`, `p`.`live_province`, `p`.`live_city`, `p`.`visits`, `p`.`visited`, `p`.`priv_profile`, `p`.`priv_blog`, `p`.`priv_location`, `p`.`last_active`, `p`.`last_bid`, `p`.`status`, `p`.`address`, `p`.`lbs_report`, `p`.`city`, `p`.`places`, `p`.`msn`, X(p.xy) AS `x`, Y(p.xy) AS `y`, `p`.`receive_msn_notify`, `p`.`state`, `p`.`karma`, `p`.`last_checkin_poi`, `p`.`timezone`, `c`.`followings`, `c`.`followers`, `c`.`favorites`, `c`.`posts`, `c`.`received_msgs`, `c`.`friends`, `c`.`majors`, `c`.`places`, `c`.`checkins`, `c`.`invites`, `c`.`badges`, `c`.`treasures`, `ub`.`get_time`, `at`.`file_id`, `at`.`filename`, `at`.`dateline` AS `at_dateline`, `at`.`mimetype`, `at`.`filesize`, `at`.`ext`
FROM `better_user_badges` AS `ub` 
INNER JOIN `better_profile` AS `p` USING(uid)
INNER JOIN `better_profile_counters` AS `c` USING(uid)
LEFT JOIN `better_attachments` AS `at` ON at.uid=p.uid AND p.avatar=at.file_id
WHERE ub.bid=$bid
ORDER BY ub.get_time DESC
LIMIT $pageSize		
EOT;
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v[$order].'_'.$v['uid']] = $v;
			}
		}

		krsort($results, SORT_NUMERIC);
		$data = array_chunk($results, $pageSize);
		$rows = isset($data[$page-1]) ? $data[$page-1] : array();

		return array(
						'pages' => count($data),
						'count' => count($rows),
						'total' => count($results),
						'rows' => $rows
						);		
	}
	
	public static function getBadgeUids($bid)
	{
		$results = array();

		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$selected = array(
				'p.uid', 'b.get_time'
				);

			$select->from(BETTER_DB_TBL_PREFIX.'account AS p', $selected);
			$select->join(BETTER_DB_TBL_PREFIX.'user_badges AS b', 'b.uid=p.uid AND b.bid='.$bid);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();

			foreach($rows as $v) {
				$results[$v['get_time'].'.'.$v['uid']] = $v['uid'];
			}
		}

		return $results;		
	}
	
	public function getMaxid()
	{
		$sql = "select max(id) as max_id from ".BETTER_DB_TBL_PREFIX."badge";
		$rs = self::squery($sql, $this->rdb);			
		$row = $rs->fetch();
		return $row['max_id'];
	}
}