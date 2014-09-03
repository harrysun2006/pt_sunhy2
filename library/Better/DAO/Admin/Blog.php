<?php

/**
 * 后台消息管理
 * 
 * @package Better.Admin
 * @author leip <leip@peptalk.cn>
 * 
 */

class Better_DAO_Admin_Blog extends Better_DAO_Admin_Base
{
	public static function resetPlace(array $bids)
	{
		$uids = array();
		
		foreach ($bids as $bid) {
			list($uid, $cnt) = explode('.', $bid);
			$uids[] = $uid;
		}
		$sids = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($uids);
		
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$wdb = $cs['w'];
			$sql = "UPDATE `".BETTER_DB_TBL_PREFIX."blog` SET `city`='', `address`='', `range`='', `xy`=GeomFromText('POINT(0 0)') WHERE `bid` IN ('".implode("','",$bids)."');";
			parent::squery($sql, $wdb);
		}		
	}
	
	public static function resetAttach(array $bids)
	{
		$uids = array();
		
		foreach ($bids as $bid) {
			list($uid, $cnt) = explode('.', $bid);
			$uids[] = $uid;
		}
		$sids = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($uids);
		
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$wdb = $cs['w'];
			$sql = "UPDATE `".BETTER_DB_TBL_PREFIX."blog` SET `attach`='' WHERE `bid` IN ('".implode("','",$bids)."');";
			parent::squery($sql, $wdb);
		}				
	}
	
	public static function getAllBlogs(array $params)
	{
		$results = $data = array();

		$cacheKey = $params['cacheKey'] ? $params['cacheKey'] : '';
		$cacheKey = self::$cachePrefix.$cacheKey;
		$page = $params['page'] ? intval($params['page']) : 1;
		$from = $params['from'] ? (int) $params['from'] : 0;
		$to = $params['to'] ? (int) $params['to'] : 0;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$user_keyword = $params['user_keyword'] ? trim($params['user_keyword']) : '';
		$place_keyword = $params['place_keyword'] ? trim($params['place_keyword']) : '';
		$photo = $params['photo']? $params['photo']: 2 ;
		$reload = $params['reload']? $params['reload']: 0;
		$checked = $params['checked']!='' ? ($params['checked']=='1' ? '1' : '0') : '';
		$source = $params['source'] ? trim($params['source']) : '';
		$order = $params['order'];
		$uid = $params['uid'] ? trim($params['uid']) : '';
		$type = $params['type'] ? $params['type'] : '';
		$poi_id = $params['poi_id'] ? $params['poi_id'] : '';
		
		$filter_uids = $params['filter_uids']?$params['filter_uids']:array();
		
		//Better_Cache_Lock::getInstance()->wait($cacheKey);

		if ($reload || !parent::getDbCacher()->test($cacheKey)) {
			Better_Cache_Lock::getInstance()->lock($cacheKey);
			
			if ($uid=='') {
				$serverIds = parent::getServerIds();
			} else {
				$serverIds = Better_DAO_User_Assign::getInstance()->getServerIdsByUids(array($uid));
			}
			
			foreach ($serverIds as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'blog AS b', array(
					'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 'b.checked', 'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range', 'b.type', 'b.poi_id', 'b.priv', 'b.is_top', 'b.featured'
					));
				$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array('p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.favorites', 'p.posts AS posts', 'p.places', 'p.avatar', 'p.priv_blog', 'p.address AS user_address', 'p.range AS user_range', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.lbs_report'));
				$select->join(BETTER_DB_TBL_PREFIX.'account AS a', 'a.uid=p.uid', array('a.email'));
				
				if ($uid!='') {
					$select->where('b.uid=?', $uid);	
				}
				
				if($poi_id){
					$select->where('b.poi_id LIKE ?', '%'.$poi_id.'%');
				}
				
				if ($checked!='') {
					$select->where('b.checked=?', $checked);
				}
				
				if ($from>0) {
					$select->where('b.dateline>=?', $from);
				}
				
				if ($to>0) {
					$select->where('b.dateline<=?', $to);
				}
				
				if ($source!='') {
					$select->where('b.source=?', $source);
				}
				
				if ($keyword!='') {
					$select->where($rdb->quoteInto('b.message LIKE ?', '%'.$keyword.'%'));
				}
				
				if ($place_keyword!='') {
					$select->where($rdb->quoteInto('b.address LIKE ?', '%'.$place_keyword.'%'));
				}
				
				if ($user_keyword!='') {
					$select->where($rdb->quoteInto('p.nickname LIKE ?', '%'.$user_keyword.'%').' OR '.$rdb->quoteInto('p.username LIKE ?', '%'.$user_keyword.'%').' OR '.$rdb->quoteInto('a.email LIKE ?', '%'.$user_keyword.'%'));
				}
				
				if($type!=''){
					$select->where($rdb->quoteInto('b.type=?', $type));
				}
				

				$select->where($rdb->quoteInto('b.message!=?', ''));
				$select->where($rdb->quoteInto('b.message not like ?', '我和%成为好友'));
		
				
				if ($photo=='1') {
					$select->where('b.attach!=\'\'');
				} else if ($photo=='0') {
					$select->where('b.attach=\'\'');
				}
				if(!empty($filter_uids)){
					$select->where('b.uid not in(?)',$filter_uids);
				}
				$select->order('b.dateline DESC');
				//$select->limit(BETTER_MAX_LIST_ITEMS);

				$rs = parent::squery($select, $rdb);
				$rows = $rs->fetchAll();
				
				foreach($rows as $row) {
					$data[$row['dateline']] = $row;
				}	
			}
			
			if ($order=='ASC') {
				ksort($data);
			} else {
				krsort($data);
			}
			//$tmp = array_chunk($data, BETTER_MAX_LIST_ITEMS);
			$results = &$data;
			unset($data);
			
			parent::getDbCacher()->set($cacheKey, $results, 300);
			Better_Cache_Lock::getInstance()->release($cacheKey);
		} else {
			$results = parent::getDbCacher()->get($cacheKey);
		}

		return $results;
	}
}
