<?php

class Better_DAO_Admin_DmessageReceived extends Better_DAO_Admin_Base
{
  	
	public static function getAllReceived(array $params)
	{
		$data = $results = array();
		
		$page = $params['page'] ? (int) $params['page'] : 1;
		$from = $params['from'] ? (int) $params['from'] : 0;
		$to = $params['to'] ? (int) $params['to'] : 0;
		$keyword = isset($params['keyword']) ? trim($params['keyword']) : '';
		$user_keyword = isset($params['user_keyword']) ? trim($params['user_keyword']) : '';
		$reload = $params['reload'] ? $params['reload'] : 0 ;
		$uid = isset($params['uid']) ? trim($params['uid']) : '';
		$msgid = $params['msgid'] ? $params['msgid']: '';
		$kuid = $params['kuid']? $params['kuid']:'';
		//$order = isset($params['order']) ? $params['order'] : 'ASC';
		
		$cacheKey = parent::$cachePrefix.'_dmessage_'.md5($msgid.'_'.$kuid.'_'.$from.'_'.$to.'_'.$keyword.'_'.$uid.'_'.$user_keyword.'_dmessage');
		
		//Better_Cache_Lock::getInstance()->wait($cacheKey);
		
		if ($reload || !parent::getDbCacher()->test($cacheKey)) {
			Better_Cache_Lock::getInstance()->lock($cacheKey);
			
			$serverIds = parent::getServerIds();
	
			foreach ($serverIds as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'dmessage_receive AS m');
				$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=m.uid', array(
					'p.username', 'p.nickname',
					));
				
					
				if ($uid!='') {
					$select->where('m.from_uid=?', $uid);	
				}
				
				if($kuid){
					$select->where('m.uid=?', $kuid);
				}
					
				if($msgid){
					list($ruid, $msg_id) = explode('.', $msgid);
					$select->where('m.uid=?', $ruid);
					$select->where('m.msg_id=?', $msg_id);		
				}
				
				if ($from) {
					$select->where('m.dateline>=?', $from);
				}
				
				if ($to) {
					$select->where('m.dateline<=?', $to);
				}
				
				if ($keyword!='') {
					$select->where('m.content LIKE ?', '%'.$keyword.'%');
				}
				
				if ($user_keyword!='') {
					$select->where($rdb->quoteInto('p.username LIKE ?', '%'.$user_keyword.'%').' OR '.$rdb->quoteInto('p.nickname LIKE ?', '%'.$user_keyword.'%'));
				}
				
				$select->where('m.type=?', 'direct_message');
				
				$select->where('m.from_uid!=?', '10000');
				
				$select->order('m.dateline DESC');
				//$select->limit(BETTER_MAX_LIST_ITEMS);

				$rs = parent::squery($select, $rdb);
				$rows = $rs->fetchAll();
				
				foreach($rows as $row) {
					$data[] = $row;
				}	
				
			}
			
			foreach ($data as $key => $value) {
				$time[$key] = $value['dateline'];
				$content[$key] = $value['content'];
			}
				
			array_multisort($time, SORT_DESC, $content, $data); 
			unset($time);
			unset($content);
			
			//$tmp = array_chunk($data, BETTER_MAX_LIST_ITEMS);
			$results = &$data;
			unset($data);

			if (is_array($results)) {
				parent::getDbCacher()->set($cacheKey, $results, 300);
			}
			
			Better_Cache_Lock::getInstance()->release($cacheKey);
			
		} else {
			$results = parent::getDbCacher()->get($cacheKey);
		}

		return $results;
	}
}