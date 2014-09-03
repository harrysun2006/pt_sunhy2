<?php

/**
 * POI置顶数据
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_Top extends Better_DAO_Poi_Base
{
	private static $instance = null;

    public function __construct($identifier = 0)
    {
    	parent::__construct ($identifier);
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_top';
		$this->priKey = 'poi_id';
		$this->orderKey = 'rank';
    	
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('poi_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function &getTop(array $params)
	{
		$rows = array();

		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$keyword = $params['keyword'];
		$page = (int)$params['page'];
		$pageSize = (int)$params['count'];
		
		$page || $page = 1;
		$pageSize || $pageSize = BETTER_PAGE_SIZE;
		
		if ($lon && $lat) {
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
		}		
		
		$select = $this->rdb->select();
		
		if (strlen($keyword)) {
			$where = ' `p`.`name` '.$this->rdb->quoteInto('LIKE ?', '%'.$keyword.'%');
			$select->where($where);
		}

		$select->where('closed=?', 0);
			
		$selected = array(
			'p.poi_id', 'p.category_id', 'p.name', 'p.major', 'p.major_change_time', new Zend_Db_Expr('X(p.xy) AS x'), new Zend_Db_Expr('Y(p.xy) AS y'), 'p.address', 
			'p.city', 'p.creator', 'p.create_time', 'p.checkins', 'p.favorites', 'p.users', 'p.posts', 'p.tips', 'p.visitors',
			'p.province', 'p.country', 'p.phone', 'p.certified', 'p.logo', 'p.intro',	 
			);
		if ($lon && $lat) {
			$selected[] = new Zend_Db_Expr("ROUND(GLength(LineStringFromWKB(LineString(p.xy, GeomFromText('POINT(".$x." ".$y.")'))))) AS distance");
			$select->where("ROUND(GLength(LineStringFromWKB(LineString(p.xy, GeomFromText('POINT(".$x." ".$y.")')))))<t.range");
		}

		$select->from($this->tbl.' AS t', $selected);
		$select->join(BETTER_DB_TBL_PREFIX.'poi AS p', 'p.poi_id=t.poi_id', array());
		$select->joinleft(BETTER_DB_TBL_PREFIX.'poi_category AS c', 'c.category_id=p.category_id', array(
			'c.category_image', 'c.category_name', 'c.tags',
			));
		$select->limitPage($page, $pageSize);

		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();

		return $rows;
	}
	
}