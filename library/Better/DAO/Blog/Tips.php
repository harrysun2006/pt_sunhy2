<?php

/**
 * 
 * 微博表的tips
 * 
 * @package Better.DAO.Blog
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Blog_Tips extends Better_DAO_Blog_Base
{
	
    public function __construct($identifier = 0)
    {
		parent::__construct ($identifier);
	}
	
	public function uid($uid)
	{
		return self::search(array(
			'uid' => $uid,
			));
	}
	
	public static function &search(array $params)
	{
		$params['type'] = 'tips';
		$params['order'] = array('b.polls DESC', 'b.dateline DESC');
		
		return parent::search($params);
	}
	
	public function rangedTips(array $params)
	{
		/**
		* 2011-07-14: 增加优质贴士逻辑：
		* 1. 人气贴士(单POI),全部显示
		*    置顶、优质、普通排序
		*    order='poll' -> 投票并分一周内、一周前
		* 2. 附近贴士(多POI)按置顶、优质、普通排序, 如有优质贴士则显示置顶和优质贴士，否则显示全部贴士(暂时全部显示)
		*    不考虑时间和投票
		*/ 
		$r1 = array();
		$r2 = array();
		$rr = array();

		$page = (int)$params['page'];
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$range = (float)$params['range'];
		$poiId = (int)$params['poi_id'];
		$order = $params['order'] ? $params['order'] : '';

		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		$has_featured = 0;
		$show_all = Better_Config::getAppConfig()->ranged_tips->showall;
		isset($show_all) || $show_all = 1;
		$now = time();
		foreach ($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog AS b', array(
				'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 
				'b.checked', 'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
				'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major', 'b.up', 'b.down', new Zend_Db_Expr('(b.up-b.down) AS poll_result'), 'b.is_top', 'b.featured'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array(
				'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.last_checkin_poi',
				'p.avatar', 'p.priv_blog',  'p.address AS user_address', 'p.range AS user_range', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.lbs_report', 'p.allow_rt', 'p.sync_badge'
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'b.uid=at.uid AND at.file_id=b.attach', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));			
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
			$select->joinleft(BETTER_DB_TBL_PREFIX.'rtblog_counters AS rtcount', 'b.bid=rtcount.bid', 'rtcount.nums as comments');
			$select->where('b.type=?', 'tips');
			$select->where('b.checked=?', 1);
			
			if ($poiId > 0) {
				$select->where('b.poi_id=?', $poiId);
			} else if ($lon && $lat) {
				list($x, $y) = Better_Functions::LL2XY($lon, $lat);
				$x1 = $x-$range/2;
				$y1 = $y+$range/2;
				$x2 = $x+$range/2;
				$y2 = $y-$range/2;
		
				$sql = "MBRWithin(`b`.`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
				$expr = new Zend_Db_Expr($sql);
				$select->where($expr);
			}
			$select->order('b.is_top DESC');
			$select->order('b.featured DESC');
			$select->order('poll_result DESC');		
		
			$select->limit(BETTER_MAX_LIST_ITEMS);
			$rs = self::squery($select, $rdb);
			
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$has_featured += $row['featured'];
				// 排序key：
				// 附近贴士：是否置顶, 是否优质, 时间, bid
				// 人气贴士: 是否置顶, 是否优质, [是否在一周内, 投票数+评论数], 时间, bid
				$key = strval($row['is_top'] ? $row['is_top'] : 0) . strval($row['featured'] ? $row['featured'] : 0);
				if ($poiId > 0) { // 单POI, 人气贴士
					if ($order == 'poll') { // 按投票数排
						$iow = ($now - $row['dateline'] <= 7*24*3600) ? 1 : 0; // 是否在一周内?
						//投票数 跟 评论数效果一致
						$key .= '.' . strval($iow) . strval(10000000+$row['poll_result']+$row['comments']);
					} 

				}
				$key .= '.' . $row['dateline'] . '.' . $row['bid'];
				$r1[$key] = $row;
				if ($row['is_top'] == 1 || $row['featured'] == 1) $r2[$key] = $row; 
			}
		}
		// 如果附近贴士并且有优质并且配置为不显示所有，则仅显示置顶和优质贴士
		if ($poiId <= 0 && $has_featured > 0 && $show_all == 0) {
			$rr = &$r2;
		} else {
			$rr = &$r1;
		}
		
		krsort($rr, SORT_STRING);
		
		if (count($rr) > BETTER_MAX_LIST_ITEMS) {
			$tmp = array_chunk($rr, BETTER_MAX_LIST_ITEMS);
			$rr = $tmp[0];
			unset($tmp);
		}
		return $rr;
	}
}