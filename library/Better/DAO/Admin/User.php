<?php

class Better_DAO_Admin_User extends Better_DAO_Admin_Base
{
	
	public static function getAllUsers($params)
	{
		$results = $data = array();
		
		$cacheKey = $params['cacheKey'] ? $params['cacheKey'] : '';
		$cacheKey = self::$cachePrefix.$cacheKey;
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$place_keyword = $params['place_keyword'] ? trim($params['place_keyword']) : '';
		$avatar = isset($params['avatar']) ? intval($params['avatar']) : -1;
		$reload = $params['reload'] ? 1 : 0;
		$from = $params['from'] ? (int) $params['from'] : 0;
		$to = $params['to'] ? (int) $params['to'] : 0;
		$uid = $params['uid']? $params['uid']:'';
		$recommend = $params['recommend']? $params['recommend']:0;
		//Better_Cache_Lock::getInstance()->wait($cacheKey);

		if (!parent::getDbCacher()->test($cacheKey) || $reload==1 ) {
			Better_Cache_Lock::getInstance()->lock($cacheKey);
			
			$serverIds = parent::getServerIds();
			foreach ($serverIds as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'account AS a', '*');
				$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=a.uid', array(
					'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
					'p.tags', 'p.avatar', 'p.favorites', 'p.live_province', 'p.live_city',
					'p.now_posts', 'p.posts', 'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
					'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn', 'p.gtalk', 'p.received_msgs', 'p.sent_msgs', 'p.new_msgs', 'p.files', 'p.last_checkin_poi',
					'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.range', 'p.last_update', 'p.rp','p.karma','p.last_active'
					));
				if(isset($params['advance']) && $params['advance']){
					//查看掌门
					if($recommend){
						if($recommend==2){
							$value=0;
						}else{
							$value=1;
						}
						$select->where('p.recommend=?', $value);
					}
					if(is_array($uid) && count($uid)>0){
						foreach($uid as $key=>$value){
							$uids[] = $value['major'];
							$timearr[$value['major']]= $value['timedate'];
							$poiarr[$value['major']]=$value['poi_id'];
						}
						$select->where('p.uid in(?)', $uids);
					}
					$select->where('p.state !="banned"');
					$rs = parent::squery($select, $rdb);
					$rows = $rs->fetchAll();			
					foreach($rows as $row) {
						$row['last_update'] = $timearr[$row['uid']]?$timearr[$row['uid']]:$row['last_update'];
						$row['poiinfo']=Better_DAO_Poi_Info::getInstance()->getPoi($poiarr[$row['uid']]);
//						if(!$row['poiinfo']['closed']){
							$data[$row['last_update'].(1000000+intval($row['uid']))] = $row;		
//						}				
					}					
				}else{				
					if ($keyword!='') {
						$select->where($rdb->quoteInto('p.birthday LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('a.cell_no LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.self_intro LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.live_city LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.live_province LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.gender LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('a.email LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.uid LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.username  LIKE ?', '%'.$keyword.'%').' OR '.$rdb->quoteInto('p.nickname  LIKE ?', '%'.$keyword.'%'));
					}
					
					if($uid){
						$select->where('p.uid=?', $uid);
					}
					if($place_keyword!=''){
						$select->where($rdb->quoteInto('p.address LIKE ?', '%'.$place_keyword.'%').' OR '.$rdb->quoteInto('p.city LIKE ?', '%'.$place_keyword.'%'));
					}
					
					if ($from>0) {
						$select->where('p.last_update>=?', $from);
					}
					
					if ($to>0) {
						$select->where('p.last_update<=?', $to);
					}
					
					if ($avatar>=0) {
						if ($avatar==0) {
							$select->where('p.avatar=\'\' OR p.avatar IS NULL');
						} else {
							$select->where('p.avatar!=\'\' AND p.avatar IS NOT NULL');
						}
					}
	
					$select->order('p.last_update DESC');
					//$select->limit(BETTER_MAX_LIST_ITEMS);
//					echo $select->__toString();die;
					$rs = parent::squery($select, $rdb);
					$rows = $rs->fetchAll();
					
					foreach($rows as $row) {
						$data[$row['last_update'].(1000000+intval($row['uid']))] = $row;
					}
				}
			}
			
			krsort($data);
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