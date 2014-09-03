<?php

/**
 * POI的数据操作
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Poi_Info extends Better_DAO_Poi_Base
{

	private static $instance = null;
	private static $got = array();
	private static $abGot = array();
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('poi_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function nearbyMajors(array $params)
	{
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$range = (int)$params['range'];
		$range || $range = 5000;
		$page = (int)$params['page'];
		$page || $page = 1;
		$limit = (int)$params['limit'];
		$limit || $limit = BETTER_PAGE_SIZE;
		$st = ($limit*$page) - $limit;
		
		$return = array(
			'rows' => array(),
			'total' => 0,
			'pages' => 0,
			);

		$sql = "SELECT major AS uid, MAX(major_change_time) AS major_change_time
		FROM `".BETTER_DB_TBL_PREFIX."poi_major` 
		WHERE 1";

		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;

			$sql .= " AND MBRWithin(xy, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
		}			
		
		$sql .= " GROUP BY major 
		ORDER BY major_change_time DESC
		LIMIT ".$st.", ".$limit."
		";					
		
		$uid = Better_Registry::get('sess')->get('uid');
		//$sql .= ";#".$uid;
		
		$rs = self::squery($sql, $this->rdb);
		$rows = $rs->fetchAll();
		foreach ($rows as $row) {
			$return['rows'][$row['major_change_time'].'.'.$row['uid']] = $row['uid'];
		}
		
		$return['total']  = count($return['rows'])+1;
			
		return $return;
	}
	
	public function getPoiByAb($abId)
	{
		if (!@isset(self::$abGot[$abId])) {
			$select = $this->rdb->select();
			$select->from($this->tbl.' AS p', array(
				'p.poi_id', 'p.category_id', 'p.name', new Zend_Db_Expr('X(p.xy) AS x'), new Zend_Db_Expr('Y(p.xy) AS y'), 'p.address', 'p.city', 'p.creator', 'p.major', 'p.create_time', 'p.posts', 'p.tips', 'p.checkins', 'p.favorites', 'p.users',
				'p.phone', 'p.intro', 'p.visitors', 'p.label', 'p.ref_id', 'p.closed', 'p.major_change_time', 'p.certified'
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'poi_category AS c', 'p.category_id=c.category_id', array(
				'c.category_name', 'c.category_image', 'c.en_category_name'
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'poi_notification AS n', 'n.poi_id=p.poi_id', array(
				'n.nid', 'n.title', 'n.content', 'n.dateline', 'n.image_url'
				));
			$select->where('p.aibang_id=?', $abId);
			$select->limit(1);
	
			$result = self::squery($select, $this->rdb);
			$row = $result->fetch();

			if ($row['poi_id']) {
				$row['category_name'] = Better_Language::loadDbKey('category_name', $row);
				self::$abGot[$abId] = $row;
				self::$got[$row['poi_id']] = &self::$abGot[$abId];
			}
		} else {
			$row = self::$abGot[$abId];
		}
		
		return $row;
	}
	
	public function getPoi($poiId)
	{
		$poiId = (int)$poiId;
		if (!isset(self::$got[$poiId])) {				
			$select = "SELECT p.poi_id, p.category_id, p.name, X(p.xy) AS x, Y(p.xy) AS y, p.address, p.city, p.creator, p.major, p.create_time, p.posts, p.tips, p.checkins, p.favorites, p.users,
					p.phone, p.intro, p.visitors, p.label, p.ref_id, p.closed, p.major_change_time, p.certified,p.ownerid, p.forbid_major, p.level, p.level_adjust,	p.logo,
					c.category_name, c.category_image, c.en_category_name, 
					n.nid, n.title, n.content, n.dateline, n.image_url,n.endtm,o.poi_id AS poiid,n.action, n.sms_no, n.sms_content, n.url, n.phone AS nphone,
					o.owner_id,
          t.rank ,
          if(isnull(e.radius), 0, e.radius) AS radius
				FROM ".$this->tbl." AS p 
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."poi_category AS c ON p.category_id=c.category_id 
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."poi_notification AS n ON n.poi_id=p.poi_id AND n.checked=1
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."poi_top AS t ON t.poi_id=p.poi_id  
					LEFT JOIN (SELECT poi_id,GROUP_CONCAT(owner_id) AS owner_id FROM ".BETTER_DB_TBL_PREFIX."poi_owner GROUP BY poi_id) AS o ON o.poi_id=p.poi_id 
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."poi_extra AS e ON e.poi_id=p.poi_id  
				WHERE p.poi_id=".$poiId." 
				LIMIT 1";
			$result = self::squery($select, $this->rdb);
			$row = $result->fetch();
			$row['category_name'] = Better_Language::loadDbKey('category_name', $row);			
			$row['top'] = intval($row['rank']) ? true : false;
		} else {
			$row = &self::$got[$poiId];
		}
		
		if(preg_match('/^([0-9]+).([0-9]+)$/', $row['image_url'])) {
			$attach = Better_Attachment_Parse::getInstance($row['image_url'])->result();
			$row['attach_tiny'] = $attach['tiny'];
			$row['attach_thumb'] = $attach['thumb'];
			$row['attach_url'] = $attach['url'];	
		} else if (preg_match('/^http(.+)$/', $row['image_url'])) {
			$row['attach_tiny'] = $row['attach_thumb'] = $row['attach_url'] = $row['image_url'];
		}

		return $row;
	}	
	
	public function insert(array $data)
	{
		$data['major'] = 0;
		$data['major_change_time'] = 0;
		$data['create_time'] = time();
		$data['checkins'] = 0;
		$data['favorites'] = 0;
		$data['users'] = 0;

		$poiId = parent::_insertXY($data);
		
    if ($data['creator']) $this->db->insert('better_poi_newly', array('poi_id' => $poiId, 'creator' => $data['creator']));
		return $poiId;
	}

	public function update($data, $val='', $cond='AND')
	{
		return parent::_updateXY($data, $val, $cond);
	}
	
	public function updateByCond($data,$where='')
	{
		return parent::updateByCond($data, $where);
	}
	
	public function getsync($poiId){
		
		$select = $this->rdb->select();
		
		$select->from(BETTER_DB_TBL_PREFIX.'poi_sync AS p',array('p.protocol','p.type','p.number'));
		$select->where('p.poi_id=?', $poiId);		
		$result = self::squery($select, $this->rdb);		
		$row = $result->fetchAll();
		return $row;
	}
	public function getUserNativedayCreateInfo($uid='10000')
	{
		$result = array(
			"t_count" => 0,
			"last_time" => 0			
		);
		$offset = (defined('BETTER_USER_TIMEZONE') ? BETTER_USER_TIMEZONE : 8)*3600;		
		$now = time();
		$dayStart = $now - date('H', $now+$offset)*3600 - date('i', $now+$offset)*60 - date('s', $now+$offset);
		
		$select = $this->rdb->select();
		$select = "SELECT count( * ) as t_count,max(create_time) as last_time
FROM `better_poi`
WHERE creator='".$uid."' and create_time>='".$dayStart."'
GROUP BY creator";	
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		if($row){
			$result = array(
				"t_count" => $row['t_count'],
				"last_time" => $row['last_time'],			
			);
		}
		return $result;		
	}
	
	
	/**
	 * 首页掌门
	 */
	public function indexMajors(array $params)
	{
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$range = (int)$params['range'];
		$range|| $range = 5000;
		$page = (int)$params['page'];
		$page || $page = 1;
		$limit = (int)$params['limit'];
		$limit || $limit = BETTER_PAGE_SIZE;
		$st = ($limit*$page) - $limit;
		
		$sql ="SELECT A.*,cm.poi_id FROM";
		$sql .= "(SELECT  MAX(m.major_change_time) AS major_change_time, m.recommend,m.major,p.closed
			FROM `".BETTER_DB_TBL_PREFIX."poi_major` m
			LEFT JOIN better_poi p ON p.poi_id = m.poi_id 
			WHERE 1
			";	
		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$x1 = $x-$range/2;
			$y1 = $y+$range/2;
			$x2 = $x+$range/2;
			$y2 = $y-$range/2;

			$sql .= " AND MBRWithin(m.xy, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
		}			
		$sql .= " AND p.closed=0";//未被关闭的POI
		$sql .= " GROUP BY m.major 
			ORDER BY m.recommend DESC,m.major_change_time DESC
			LIMIT ".$st.", ".$limit."
			) A";		
		$sql .=" LEFT JOIN better_poi_major cm on cm.major=A.major and cm.major_change_time=A.major_change_time";
//		echo $sql;die;
		$rs = self::squery($sql, $this->rdb);
		$rows = $rs->fetchAll();
		return $rows;
	}
	
	/**
	 * 
	 */
	public function getMajorByIds($ids)
	{
		$select = $this->rdb->select();
		$select->where('poi_id IN (?)', $ids);
		$select->from($this->tbl);
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		
		return $rows;			
	}
	public function getlogo($poi_id){
		$result = array();
		if($poi_id>0){
			$sql = "select logo from ".$this->tbl." where poi_id=".$poi_id;
			$rs = self::squery($sql, $this->rdb);
			$result = $rs->fetch();
		}
		return $result;
	}
}
