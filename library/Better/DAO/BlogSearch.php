<?php

/**
 * 消息搜索数据操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_Dao_BlogSearch extends Better_DAO_Blog
{
	private static $instance = array();
	
	public function __construct($identifier=0)
	{
		parent::__construct($identifier);
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_Dao_BlogSearch($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	public function searchPhotoByBids()
	{
		
	}

	public function search($keyword)
	{
		$params = is_array($keyword) ? $keyword : array('keyword' => $keyword);
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		$results = array();
		$keyword = $params['keyword'];
		$poiId = (int)$params['poi_id'];

		foreach($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog AS b', array(
				'b.bid', 'b.dateline'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array());
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
			$select->where('b.checked=?', '1');
			
			if ($params['lon'] && $params['lat']) {
				$range = isset($params['range']) ? (int)$params['range'] : 5000;
					
				list($x, $y) = Better_Functions::LL2XY($params['lon'], $params['lat']);
				$x1 = $x-$range/2;
				$y1 = $y+$range/2;
				$x2 = $x+$range/2;
				$y2 = $y-$range/2;
	
				$sql = "MBRWithin(`b`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
				$expr = new Zend_Db_Expr($sql);
				$select->where($expr);				
			}
			
			if (strlen($keyword)) {
				$select->where($rdb->quoteInto('b.message LIKE ?', '%'.$keyword.'%'));
			}
			
			if ($params['type'] && $params['type']!='all') {
				$select->where('type=?', $params['type']);
			}
			
			if (isset($params['uid'])) {
				$select->where('b.uid=?', (int)$params['uid']);
			}
			
			if ($params['type']=='tips') {
				$select->order('poll_result DESC');
			} 
			
			if ($poiId>0) {
				$select->where('b.poi_id=?', $poiId);
			}


			$select->order('b.dateline DESC');
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
					
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$bid = $v['bid'];
				if (!in_array($bid, $results)) {
					$results[$v['dateline']] = $bid;
				}
			}
		}
		krsort($results);
		//	取出合并后的limit条数据
		$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
		unset($results);

		return isset($data[0]) ? $data[0] : array();
	
	}
	
}