<?php

/**
 * 推荐贴士
 * 
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_Tips_Recommends extends Better_DAO_Base
{
	private static $instance = array();

 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
		$this->tbl = BETTER_DB_TBL_PREFIX.'tips_recommends';
		$this->priKey = 'bid';
		$this->orderKey = &$this->priKey;
		
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
	
	public function getRecommends(array $params)
	{
		$type = 'tips';
		
		$results = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 2;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		
		$rangeWhere = '';
		if (Better_LL::isValidLL($lon, $lat)) {
			$range = $params['range'] ? (int)$params['range'] : 5000;
			
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;			
			
			$rangeWhere = " AND MBRWithin(`b`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
		}
		
		$max = $page*$pageSize;
		$uid = (int)$this->identifier;

		$sql = "SELECT fe.uid AS feuid, b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
			 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
			 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
			 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
			 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
		FROM ".BETTER_DB_TBL_PREFIX."tips_recommends AS r
			INNER JOIN ".BETTER_DB_TBL_PREFIX."blog AS b
				ON b.bid=r.bid
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."user_banned AS ub
				ON ub.uid=b.uid AND ub.uid IS NULL			
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
				ON p.uid=b.uid
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."follower AS fe
				ON b.uid=fe.uid AND fe.follower_uid=".$uid."
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."friends AS f
				ON b.uid=f.uid AND f.friend_uid=".$uid."
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."blockedby AS bl
				ON b.uid=bl.uid AND bl.blocked_by_uid=".$uid."
			LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
				ON b.uid=at.uid AND at.file_id=b.attach	
		WHERE b.type='tips' AND b.checked=1 AND bl.uid IS NULL ".$rangeWhere."
		ORDER BY b.dateline DESC
		LIMIT ".$max."
		";

		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$results[$row['dateline'].'.'.$row['bid']] = $row;
			}			
		}
		
		if (count($results)>0) {
			krsort($results);
			if (count($results)>BETTER_MAX_LIST_ITEMS) {
				$tmp = array_chunk($results, BETTER_MAX_LIST_ITEMS);
				$results = $tmp[0];
				unset($tmp);
			}
		}		
		
		return $results;				
	}
}