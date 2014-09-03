<?php
	
	class Better_DAO_Admin_Usercheckin extends Better_DAO_Admin_Base{
		
		public function getAllUserCheckins(array $params){
			
			$results = $data = array();
			
			$page = $params['page'] ? intval($params['page']) : 1;
			$userkeyword = $params['userkeyword'] ? trim($params['userkeyword']) : '';
			$from = $params['from'] ? (int) $params['from'] : 0;
			$to = $params['to'] ? (int) $params['to'] : 0;
			$cacheKey = $params['cacheKey'] ? $params['cacheKey'] : '';
			$cacheKey = self::$cachePrefix.$cacheKey;
			$reload = $params['reload'];
			
			Better_Cache_Lock::getInstance()->wait($cacheKey);
			
		if (!parent::getDbCacher()->test($cacheKey) || $reload==1) {
			Better_Cache_Lock::getInstance()->lock($cacheKey);
			
			$serverIds = parent::getServerIds();
			foreach ($serverIds as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'user_place_log AS up', '*');
				$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=up.uid', array(
					'p.username', 'p.nickname', 'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
					'p.tags', 'p.avatar', 'p.favorites', 'p.live_province', 'p.live_city',
					'p.now_posts', 'p.posts', 'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
					'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn', 'p.gtalk', 'p.received_msgs', 'p.sent_msgs', 'p.new_msgs', 'p.files',
					'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.range'
					));
					
				if ($userkeyword!='') {
					$select->where($rdb->quoteInto('p.uid LIKE ?', '%'.$userkeyword.'%').' OR '.$rdb->quoteInto('p.username LIKE ?', '%'.$userkeyword.'%').' OR '.$rdb->quoteInto('p.nickname LIKE ?', '%'.$userkeyword.'%'));
				}
				
				if ($from>0) {
					$select->where('up.checkin_time>=?', $from);
				}
				
				if ($to>0) {
					$select->where('up.checkin_time<=?', $to);
				}
				

				$select->order('p.uid ASC');
				$select->limit(BETTER_MAX_LIST_ITEMS);

				$rs = parent::squery($select, $rdb);
				$rows = $rs->fetchAll();
				
				foreach ($rows as $row){
					$data[$row['id']]=$row;
				}
				
			}
			
			ksort($data);
			$tmp = array_chunk($data, BETTER_MAX_LIST_ITEMS);
			$results = &$tmp[0];
			unset($data);
			
			parent::getDbCacher()->set($cacheKey, $results, 300);
			
			Better_Cache_Lock::getInstance()->release($cacheKey);
		} else {
			$results = parent::getDbCacher()->get($cacheKey);
		}
				
		return $results;
		
		}
		
	}
?>