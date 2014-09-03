<?php
	
	class Better_DAO_Admin_Business extends Better_DAO_Admin_Base{
 		
		private static $instance = null;
	
		public function __construct()
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'shopkeeper_require';
			$this->priKey = 'id';
			$this->orderKey = &$this->priKey;
		}
	
		public static function getInstance()
		{
			//if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('business_server', true);
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		//}

			return self::$instance;
		}
		
		public function getAllPOIs(array $params=array()){
			
			$results1 = $results2 = $data = array();
			
			$results = array('count'=>0, 'rows'=>array());
			
			$page = $params['page'] ? intval($params['page']) : 1;
			$from = $params['from'] ? (int) $params['from'] : 0;
			$to = $params['to'] ? (int) $params['to'] : 0;
			$namekeyword = $params['namekeyword'] ? trim($params['namekeyword']) : '';
			$poi_id = $params['poi_id'] ? trim($params['poi_id']) : '';
			$placekeyword = $params['placekeyword'] ? trim($params['placekeyword']) : '';
			$reload = $params['reload'] ? $params['reload'] : 0;
			$pageSize = $params['pageSize'] ? intval($params['pageSize']) : BETTER_PAGE_SIZE;
			$poi_from = $params['poi_from']? $params['poi_from'] :'';
			$doubt = $params['doubt']? $params['doubt']:'';
			$dyna = $params['dyna']? $params['dyna']:'';

		$rdb = $this->rdb;
		$select = $rdb->select();
		$select2= $rdb->select();
		$select3= $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'poi AS p', '*');
		$select2->from(BETTER_DB_TBL_PREFIX.'poi AS p', array(new Zend_Db_Expr("COUNT(*) AS count")));
		$select3->from(BETTER_DB_TBL_PREFIX.'poi AS p', array(new Zend_Db_Expr("COUNT(*) AS count")));
		
		//$select->join(BETTER_DB_TBL_PREFIX.'poi_category AS c', 'p.category_id=c.category_id', '*');
		
		if ($poi_id) {
			$select->where('p.poi_id=?', $poi_id);
			$select2->where('p.poi_id=?', $poi_id);
			$select3->where('p.poi_id=?', $poi_id);
		}
		
		if ($from>0) {
			$select->where('p.create_time>=?', $from);
			$select2->where('p.create_time>=?', $from);
			$select3->where('p.create_time>=?', $from);
		}
		
		if ($to>0) {
			$select->where('p.create_time<=?', $to);
			$select2->where('p.create_time<=?', $to);
			$select3->where('p.create_time<=?', $to);
		}
		
		
		if ($namekeyword!='') {
			$select->where($rdb->quoteInto('p.name LIKE ?', '%'.$namekeyword.'%').' OR '.$rdb->quoteInto('p.poi_id LIKE ?', '%'.$namekeyword.'%'));
			$select2->where($rdb->quoteInto('p.name LIKE ?', '%'.$namekeyword.'%').' OR '.$rdb->quoteInto('p.poi_id LIKE ?', '%'.$namekeyword.'%'));
			$select3->where($rdb->quoteInto('p.name LIKE ?', '%'.$namekeyword.'%').' OR '.$rdb->quoteInto('p.poi_id LIKE ?', '%'.$namekeyword.'%'));
		}
		
		
		if ($placekeyword!='') {
			$select->where($rdb->quoteInto('p.address LIKE ?', '%'.$placekeyword.'%').' OR '.$rdb->quoteInto('p.city LIKE ?', '%'.$placekeyword.'%'));
			$select2->where($rdb->quoteInto('p.address LIKE ?', '%'.$placekeyword.'%').' OR '.$rdb->quoteInto('p.city LIKE ?', '%'.$placekeyword.'%'));
			$select3->where($rdb->quoteInto('p.address LIKE ?', '%'.$placekeyword.'%').' OR '.$rdb->quoteInto('p.city LIKE ?', '%'.$placekeyword.'%'));
		}
		
		if($poi_from){
			$select->where('p.creator!=?', 0);
			$select2->where('p.creator!=?', 0);	
			$select3->where('p.creator!=?', 0);						
		}
		
		if($doubt){
			$select->where('p.city=?','');
			$select->where('p.address=?','');
			$select2->where('p.city=?','');
			$select2->where('p.address=?','');
			$select3->where('p.city=?','');
			$select3->where('p.address=?','');					
		}
		
		$select->where('p.closed=?', 0);
		$select2->where('p.closed=?', 1);
		
		if($dyna){
			$select->where('p.last_update!=?', 0);
			$select2->where('p.last_update!=?', 0);
			$select3->where('p.last_update!=?', 0);
			
			$select->order('p.last_update DESC');
		}else{
			//$select->order('p.create_time DESC');
			$select->order('p.poi_id DESC');
		}

		$select->limitPage($page, $pageSize);
		
		$sql=$select->__toString();
	
		$rs = parent::squery($select2, $rdb);
		$results2 = $rs->fetch();
		$rs = parent::squery($select3, $rdb);
		$results3 = $rs->fetch();
		
		$results1 = $rdb->fetchAll($sql);

		$results['count'] = $results3['count']-$results2['count'];
		$results['rows'] = $results1;
				

		return $results;
	}
		
		
	 public function getCategoties(){
			$cache = parent::getDbCacher();
			$cacheKey = 'better_poi_categories';
			$return = array();
			if(!$cache->test($cacheKey)){			
				$rdb = $this->rdb;
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'poi_category AS c', '*');
				$results = $rdb->fetchAll($select);
				
				foreach($results as $row){
					$return[$row['category_id']] = $row['category_name'];
				}
				$cache->set($cacheKey, $return, 48*3600);
			}else{
				$return = $cache->get($cacheKey);
			}
			return $return;
		}
		
		
		public function resetMajor($poid, $major){
			$this->update(array('major'=> 0), $poid);
			Better_DAO_User_MajorLog::getInstance($major)->deleteByCond(array('uid'=>$major, 'poi_id'=>$poid));
			$majorUser = Better_User::getInstance($major);
			$majorUserInfo = $majorUser->getUserInfo();
			$majorUser->updateUser(array(
						'majors' => $majorUserInfo['majors']-1,
			));
		}		
		
		
		public function getTotalCount($reload){
		
			if($reload || !parent::getDbCacher()->test('poiCount')){
				$poi_count = $this->getCount();
				parent::getDbCacher()->set('poiCount', $poi_count, 300);
				
			} else{
				$poi_count = parent::getDbCacher()->get('poiCount');
			}
			
			return $poi_count;
		}
		
		public function update($data, $val='', $cond='AND'){
			
			return parent::_updateXY($data, $val, $cond);
		}
		
		
		public function refPOI($poi_id, $ref_poi_id){
			
			if($poi_id && $ref_poi_id && $poi_id!=$ref_poi_id){
				$this->update(array('ref_id'=>$ref_poi_id), $poi_id);
				return true;
			}else{
				return false;
			}
		}
		
		
		//关闭POI
		public function closePOI($poi){
			$poi_id = $poi['poi_id'];
			$major = $poi['major'];
			if($poi_id){
				$this->update(array('closed'=>1), $poi_id);
				if($major){
					$this->resetMajor($poi_id, $major);
				}
				
				//关闭后处理 2011-1-4
				//请blog缓存
				Better_Cache_Clear::changeBlogType($poi_id, '');
				//Better_Cache_Clear::changeBlogType($poi_id, 'normal');
				$sIds = parent::getServerIds();
				if($sIds){
					foreach($sIds as $sid){
						$cs = parent::assignDbConnection('user_server_'.$sid, true);
						$wdb = $cs['w'];
						$rdb = $cs['r'];
						$this->rdb = $rdb;
						$this->wdb = $wdb;
						//blog
						parent::_updateXY(array('type'=>'normal', 'poi_id'=>''), array('poi_id'=>$poi_id, 'type'=>'tips'), 'AND', BETTER_DB_TBL_PREFIX.'blog');
						parent::_updateXY(array('poi_id'=>''), array('poi_id'=>$poi_id, 'type'=>'normal'), 'AND', BETTER_DB_TBL_PREFIX.'blog');
					}
					//重置db
					$db = parent::registerDbConnection('poi_server', true);
					$this->rdb = $db;
					$this->wdb = $db;
				}
				
				return true;
			}else{
				return false;
			}
			
		}
		
		
		//合并POI
		public function mergePOI($poi, $target_pid){
			$poi_id = $poi['poi_id'];
			
			$target_poi = Better_Poi_Info::getInstance($target_pid)->getBasic();
			
			if($poi_id && $target_poi['poi_id']){
				$this->update(array('closed'=>1), $poi_id);
				
				Better_DAO_Admin_Poimerge::getInstance()->doMerge($poi, $target_poi);
				return true;
			}else{
				return false;
			}
			
			
		}
		
		public function getAllVenue(array $params=array()){
			$results = array('count'=>0, 'rows'=>array());			
			$page = $params['page'] ? intval($params['page']) : 1;	
			$page = $page-1;	
			$poi_id	 =  $params['poi_id'] ? intval($params['poi_id']) : 0;	
			$pageSize = $params['pageSize'] ? intval($params['pageSize']) : BETTER_PAGE_SIZE;			
			$select = " select a.*,b.content,b.image_url,b.begintm,b.endtm from ".BETTER_DB_TBL_PREFIX."shopkeeper_require AS a  left join ".BETTER_DB_TBL_PREFIX."shopkeeper_notification AS b on b.r_id=a.id where 1"; 
			if($poi_id>0){
				$select .=" and a.poi_id=".$poi_id;				
			}
			$select .=" order by a.dateline desc limit ".$page*$pageSize.",".$pageSize;			
			
			$rs = self::squery($select, $this->rdb);			
			$row = $rs->fetchAll();	
			$selectcount = "select count(*) as t_count from ".BETTER_DB_TBL_PREFIX."shopkeeper_require AS a  where 1";			
			$rscount = self::squery($selectcount, $this->rdb);
			
			$rowcount = $rscount->fetch();	
			$result['count'] = $rowcount['t_count'];		
			$result['rows'] = $row;		
			return $result;
			
		}
		public function checkVenue(array $params=array()){
			$results = 0;			
			$id = $params['id'];			
			$doing = (int)$params['doing'];	
			$dospec = (int)$params['dospec'];	
			$checkinfo = $params['checkinfo'];			
			$venueinfo = Better_Business_Venue::getVenue($params);	
			
			if($dospec){
				$special = Better_DAO_Business_Notification::getInstance()->getInfo($id);
				if($special['poi_id']){					
					$specialToInsert = array(
					'poi_id' => $special['poi_id'],
					'uid' => $special['uid'],				
					'title' => $special['title'],
					'content' => $special['content'],
					'image_url' => $special['image_url'],
					'checked' => 1,	
					'dateline' => time(),	
					'begintm' => $special['begintm'],
					'endtm' => $special['endtm'],		
					);	
				
					Better_DAO_Poi_Notification::getInstance()->insert($specialToInsert);	
				}					
			}			
			$sql = "set status=".$doing;	
			if($doing==2){
				$sql.= ",checkinfo='".$checkinfo."'";
			}
			$lang = Better_Language::load();	
			$uid = $venueinfo['venue']['uid'];			
			$poi_id = $venueinfo['venue']['poi_id'];	
			
			$poiinfo = Better_Poi_Info::getInstance($poi_id)->get();
			if($doing==1){
				
				if($dospec){
					$msg = $lang->global->venue->check_all_sucess;
				} else {
					$msg = $lang->global->venue->check_req_sucess;
				}	
				if($poiinfo['ownerid']==0 || $poiinfo['ownerid']==''){
					$data['ownerid'] = $uid;
				} else {
					$data['ownerid'] =$poiinfo['ownerid'].",".$uid;
				}
				//Better_Admin_Poi::updatePOI($data, $poi_id);
				Better_Admin_Poi::updatePoiOwner($data['ownerid'],$poi_id);	
			} else {
				$msg = $lang->global->venue->check_req_false;
			}
			
			
			$msg = str_replace("{POINAME}",$poiinfo['name'],$msg);
			$msg = str_replace("{REASON}",$checkinfo,$msg);	
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($msg, (int)$uid);						
			
			$where = "id=".$id;		
			$select = "update ".BETTER_DB_TBL_PREFIX."shopkeeper_require ".$sql." where ".$where;				
			$rs = self::squery($select,$this->rdb);
			return $rs;
			
		}
				
		public function updateSpecial(array $params=array()){
			$results = 0;			
			$nid = $params['nid'];
			$getpoisql = "select poi_id from ".BETTER_DB_TBL_PREFIX."poi_notification where nid=".$nid ." limit 1";
			
			$getrs = self::squery($getpoisql,$this->rdb);
			$row = $getrs->fetchAll();	
			$temp_poi_id = $row['0']['poi_id'];			
			$content = $params['content'];
			$attach = $params['attach'];
			$poi_id = $params['poi_id'];
			$begintm = $params['begintm'];
			$endtm = $params['endtm'];
			$action = $params['action'];
			$sql = "set content='".$content."',image_url='".$attach."',begintm='".$begintm."',endtm='".$endtm."',action='".$action."'";
			$select = "update ".BETTER_DB_TBL_PREFIX."poi_notification ".$sql." where nid=".$nid;				
			$rs = self::squery($select,$this->rdb);
			$result=1;
			Better_DAO_Poi_Fulltext::getInstance()->updateItem($temp_poi_id, 1);
			return $result;		
		}
		
		
}
?>