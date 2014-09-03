<?php

/**
 * POI搜索
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_Search extends Better_DAO_Poi_Base
{
	protected static $instance = null;
	
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

	/**
	 * 搜索POI
	 * 
	 * @param $param
	 * @return array
	 */
	public function &search(array $param)
	{
		$result = array(
			'total' => 0,
			'rows' => array()
			);
		$lon = (float)$param['lon'];
		$lat = (float)$param['lat'];
		$range = (int)$param['range'];
		$keyword = $param['keyword'];
		$label = $param['label'];
		$limit = (int)$param['limit'];
		$major = (int)$param['major'];
		$page = (int)$param['page'];
		$count = (int)$param['count'];
		$pois = $param['poi_id'] ? (array)$param['poi_id'] : array();
		$certified = isset($param['certified']) ? (bool)$param['certified'] : null;
		$order = isset($param['order']) ? $param['order'] : 'distance';
		$excludes = (array)$param['excludes'];
		$excludesOur = (array)$param['excludes_our'];
		$uid = (int)$param['uid'];
		$withoutAb = isset($param['without_ab']) ? (bool)($param['without_ab'] ? true : false) : false;
		$category = (int)$param['category'];
		$creator = (int)$param['creator'];
		$withoutpid = $param['withoutpid'] ? $param['withoutpid'] : '';

		$select = $this->rdb->select();
		
		if ($withoutAb) {
			$select->where('aibang_id=?', '');
		}
		
		if($withoutpid){
			$select->where('poi_id!=?', $withoutpid);
		}
		
		if (strlen($keyword)) {
			$where = ' `name` '.$this->rdb->quoteInto('LIKE ?', '%'.$keyword.'%');
			$select->where($where);
		}
		
		if (strlen($label)) {
			$where = 'BINARY(`label`) '.$this->rdb->quoteInto('LIKE ?', '%'.$label.'%');
			$select->where($where);
		}

		if ($lon && $lat) {
			//return $result;
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			$range || $range = 5000;
			$x1 = $x-$range;
			$y1 = $y+$range;
			$x2 = $x+$range;
			$y2 = $y-$range;

			//$sql = "MBRWithin(xy, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
			$sql = " ( (X(xy) BETWEEN $x1 AND $x2 ) AND ( Y(xy) BETWEEN $y2 AND $y1 ))  ";
			$expr = new Zend_Db_Expr($sql);
			$select->where($expr);
		}
		
		if ($major) {
			$select->where('major=?', $major);
		}
		
		if ($creator) {
			$select->where('creator=?', $creator);
		}
		
		if ($certified===true) {
			$select->where('certified=?', 1);
		} 
		
		if (count($pois)>0) {
			$select->where('poi_id IN (?)', $pois);
		}

		if (count($excludes)>0 && $excludes[0]) {
			$select->where('aibang_id NOT IN (?)', $excludes);
		}
		
		$select->where('closed=?', 0);
		
		if (count($excludesOur)>0 && $excludesOur[0]) {
			$select->where('poi_id NOT IN (?)', $excludesOur);
		}

		$select2 = clone($select);
		
		if ($category) {
			$select2->where('category_id=?', $category);
			$select->where('p.category_id=?', $category);
		}

		$select2->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));

		if ($select2->__toString()=='SELECT COUNT(*) AS total FROM `better_poi` WHERE (closed=0);' || $select2->__toString()=='SELECT COUNT(*) AS total FROM `better_poi` WHERE (closed=0)') {
			$result['total'] = 10000000;
			/*
			$cacher = Better_Cache::remote();
			$total = $cacher->get('poi_totals');
			if ($total) {
				$result['total'] = $total;
			} else {
				$rs = self::squery($select2, $this->rdb);
				$row = $rs->fetch();
				$result['total'] = $row['total'];			

				$cacher->set('poi_totals', $row['total'], 3600*24);
			}*/
		} else {
			$rs = self::squery($select2, $this->rdb);
			$row = $rs->fetch();
			$result['total'] = $row['total'];
		}

		if ($result['total']>0) {			
			if ($page && $count) {
				$select->limitPage($page, $count);	
			} else {
				$select->limit(BETTER_MAX_LIST_ITEMS);
			}

			switch ($order) {
				case 'major_change_time':
					$select->order('major_change_time DESC');
					break;
				case 'tips':
					$select->order('tips DESC');
					break;
				case 'normal':
					$select->order('posts DESC');
					break;
				case 'checkin':
				case 'checkins':
					$select->order('checkins DESC');
					break;
				case 'force_distance':
					$select->order('distance ASC');
					break;
				case 'distance':
				default:
					if ($lon && $lat) {
						$select->order('distance ASC');
					} else {
						$select->order('checkins DESC');
					}
					break;
			}
			
			$selected = array(
				'poi_id', 'category_id', 'name', 'major', 'major_change_time', new Zend_Db_Expr('X(xy) AS x'), new Zend_Db_Expr('Y(xy) AS y'), 'address', 
				'city', 'creator', 'create_time', 'checkins', 'favorites', 'users', 'posts', 'tips', 'visitors',
				'province', 'country', 'phone', 'certified', 'logo', 'intro',			
				);
			if ($lon && $lat) {
				$selected[] = new Zend_Db_Expr("ROUND(GLength(LineStringFromWKB(LineString(xy, GeomFromText('POINT(".$x." ".$y.")'))))) AS distance");
			}

			$select->from($this->tbl.' AS p', $selected);
			$select->joinleft(BETTER_DB_TBL_PREFIX.'poi_category AS c', 'c.category_id=p.category_id', array(
				'c.category_image', 'c.category_name', 'c.tags',
				));

			Better_Log::getInstance()->logInfo($select,'p_poi_search');
			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();
			$result['rows'] = &$rows;
		}

		return $result;
	}
	
}