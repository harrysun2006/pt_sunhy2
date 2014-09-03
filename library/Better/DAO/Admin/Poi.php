<?php
	
	class Better_DAO_Admin_Poi extends Better_DAO_Admin_Base{
 		
		private static $instance = null;
	
		public function __construct()
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'poi';
			$this->priKey = 'poi_id';
			$this->orderKey = &$this->priKey;
		}
	
		public static function getInstance()
		{
			//if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('poi_server', true);
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		//}

			return self::$instance;
		}
		
    public function getAllPOIs(array $params=array())
    {
      $results = array('count'=>0, 'rows'=>array());
      $dare_select2 = false; // do we dare to execute select2? as that would be extremely slow.

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
      $select2= $rdb->select(); // for counter
      // convert coords from xy to lon,lat.
      $select->from(BETTER_DB_TBL_PREFIX.'poi AS p', '*,x(xy),y(xy),round((atan(exp(y(xy)/pow(2, 17)/256 *PI()* 2))*2-PI()/2)*180/pi(),6) lat,round(x(xy)/93206.7556,6) lon');
      $select2->from(BETTER_DB_TBL_PREFIX.'poi AS p', array(new Zend_Db_Expr("COUNT(*) AS count")));

      //$select->join(BETTER_DB_TBL_PREFIX.'poi_category AS c', 'p.category_id=c.category_id', '*');

      if ($poi_id) {
        $select->where('p.poi_id=?', $poi_id);
        $select2->where('p.poi_id=?', $poi_id);
        $dare_select2 = true;
      }

      if ($from>0) {
        $select->where('p.create_time>=?', $from);
        $select2->where('p.create_time>=?', $from);
      }

      if ($to>0) {
        $select->where('p.create_time<=?', $to);
        $select2->where('p.create_time<=?', $to);
      }


      if ($namekeyword!='') {
        $select->where($rdb->quoteInto('p.name LIKE ?', '%'.$namekeyword.'%').' OR '.$rdb->quoteInto('p.poi_id LIKE ?', '%'.$namekeyword.'%'));
        $select2->where($rdb->quoteInto('p.name LIKE ?', '%'.$namekeyword.'%').' OR '.$rdb->quoteInto('p.poi_id LIKE ?', '%'.$namekeyword.'%'));
      }


      if ($placekeyword!='') {
        $select->where($rdb->quoteInto('p.address LIKE ?', '%'.$placekeyword.'%').' OR '.$rdb->quoteInto('p.city LIKE ?', '%'.$placekeyword.'%'));
        $select2->where($rdb->quoteInto('p.address LIKE ?', '%'.$placekeyword.'%').' OR '.$rdb->quoteInto('p.city LIKE ?', '%'.$placekeyword.'%'));
      }

      if($poi_from){
        // poi_from=user_new
        // 查询用户创建的POI
        $select->joinUsing('better_poi_newly', 'poi_id');
        $select->order('create_time desc');
        $select2->joinUsing('better_poi_newly', 'poi_id');
        $dare_select2 = true;
      }

      if($doubt){
        $select->where('p.city=?','');
        $select->where('p.address=?','');
        $select2->where('p.city=?','');
        $select2->where('p.address=?','');
      }

      $select->where('p.closed=?', 0);
      $select2->where('p.closed=?', 0);

      $select->limitPage($page, $pageSize);

      $results['rows'] = $rdb->fetchAll($select);
      if($dare_select2)
        $results['count'] = $rdb->fetchOne($select2); 
      else
        $results['count'] = 999999; 

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
		
		public function getAllSpecial(array $params=array()){
			$results = array('count'=>0, 'rows'=>array());			
			$page = $params['page'] ? intval($params['page']) : 1;	
			$page = $page-1;		
			$pageSize = $params['pageSize'] ? intval($params['pageSize']) : BETTER_PAGE_SIZE;
			$keywords = trim($params['keywords']) ? trim($params['keywords']) : '';
			$what = "1";
			if($keywords){
				$what .= " and (special.content like '%".$keywords."%' or poi.name like '%".$keywords."%' or poi.poi_id='".$keywords."') ";
			}
			$select = "select poi.*,special.* from ".BETTER_DB_TBL_PREFIX."poi_notification AS special left join " .BETTER_DB_TBL_PREFIX."poi as poi on special.poi_id=poi.poi_id where ".$what." order by special.dateline desc limit ".$page*$pageSize.",".$pageSize;				
			$rs = self::squery($select, $this->rdb);
			Better_Log::getInstance()->logInfo($select,'sqlspecial');
			$row = $rs->fetchAll();	
			$selectcount = "select count(*) as t_count from ".BETTER_DB_TBL_PREFIX."poi_notification AS special left join " .BETTER_DB_TBL_PREFIX."poi as poi on special.poi_id=poi.poi_id where ".$what;
			
			$rscount = self::squery($selectcount, $this->rdb);
			$rowcount = $rscount->fetch();	
			$result['count'] = $rowcount['t_count'];		
			$result['rows'] = $row;		
			return $result;
			
		}
		public function checkSpecial(array $params=array()){
			$results = 0;			
			$nid = $params['nid'];
			$uid = $params['uid'];
			$doing = (int)$params['doing'];
			$checkinfo = $params['checkinfo'];
			$poi_id = $params['poi_id'];
			$sql = "set checked=".$doing;	
			$lang = Better_Language::load();
			
			$specialDetail = array();		
			$params = array(
				'nid'    => $nid,
				'poi_id' => $poi_id,
				'uid'    => $uid
			);
			if ($nid>0) {
				$specialInfo = Better_Poi_Notification::getInstance($poi_id);	
				Better_DAO_Poi_Fulltext::getInstance()->updateItem($poi_id, 1);			
				$specialDetail =$specialInfo->getInfo($params);	
			}
			//Zend_debug::dump($specialDetail);
			$poi_name = $specialDetail['name'];
			$special_text =  $specialDetail['content'];
			$owner_id = $specialDetail['owner'];
			$checksucess = $lang->javascript->global->special->checksucess;
			$checkrefuse = $lang->javascript->global->special->checkrefuse;
			$specialovertm = $lang->javascript->global->special->overtm;
			$sendmail = 0;
			$where = "nid=".$nid;
			if($doing==2){
				$sql .=",checkinfo='".$checkinfo."'";		
				$checkrefuse = 	str_replace('{POINAME}',$poi_name,$checkrefuse);
				$checkrefuse = 	str_replace('{TEXT}',$special_text,$checkrefuse);
				$checkrefuse = 	str_replace('{RESON}',$checkinfo,$checkrefuse);		
				$content = 	$checkrefuse;	
				$sendmail =1;
			} else if($doing==1){
				$checksucess = 	str_replace('{POINAME}',$poi_name,$checksucess);
				$content = 	$checksucess;
				$sendmail = 1;
			} else if($doing==4){
				$where .= " "; 
			} else if($doing==5){
				$specialovertm = str_replace('{POINAME}',$poi_name,$specialovertm);
				$sql .=",checkinfo='".$specialovertm."'";							
				$content = 	$checkinfo;	
				$sendmail =1;				
			}
			$owner = split('\,',$owner_id);
	 		if(!is_array($owner)){
	 				$sendmail && Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, (int)$owner);						
	 		} else {
				foreach($owner as $row){					
					$sendmail && Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, (int)$row);						
				}	
	 		}				
			$select = "update ".BETTER_DB_TBL_PREFIX."poi_notification ".$sql." where ".$where;				
			$rs = self::squery($select,$this->rdb);
			//$row = $rs->fetchAll();	
			//Zend_Debug::dump($row);
			return $rs;
			
		}
				
		public function updateSpecial(array $params=array()){
			
			$results = 0;			
			$nid = $params['nid'];
			$groupid = $params['groupid'];
			if($params['updategroup']==1 && $groupid>0){				
				$getpoisql = "select poi_id,nid from ".BETTER_DB_TBL_PREFIX."poi_notification where groupid=".$groupid;
			} else {
				$getpoisql = "select poi_id,nid from ".BETTER_DB_TBL_PREFIX."poi_notification where nid=".$nid ." limit 1";
			}
			
			$getrs = self::squery($getpoisql,$this->rdb);
			$rows= $getrs->fetchAll();	
			foreach($rows as $row){				
				$temp_poi_id = $row['poi_id'];
				$nid = 	$row['nid'];
			
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
			}
			
			return $result;		
		}
		public function checkallSpecial(array $params=array()){
			$results = 0;		
			$groupid = $params['groupid'];
			$uid = $params['uid'];
			$doing = (int)$params['doing'];
			$checkinfo = $params['checkinfo'];
			$poi_id = $params['poi_id'];
			$sql = "set checked=".$doing;	
			$lang = Better_Language::load();
			$sqlnid = "select nid,poi_id from  ".BETTER_DB_TBL_PREFIX."poi_notification where groupid=".$groupid;		
			$rs = self::squery($sqlnid,$this->rdb);
			$allnid = $rs->fetchAll();
			$sendmailonce = 1;
			foreach($allnid as $row){				
				$nid = $row['nid'];
				$poi_id = $row['poi_id'];
				$specialDetail = array();		
				$params = array(
					'nid'    => $nid,
					'poi_id' => $poi_id,
					'uid'    => $uid
				);
				if ($nid>0) {
					$specialInfo = Better_Poi_Notification::getInstance($poi_id);	
					Better_DAO_Poi_Fulltext::getInstance()->updateItem($poi_id, 1);			
					$specialDetail =$specialInfo->getInfo($params);	
				}
				//Zend_debug::dump($specialDetail);
				$poi_name = $specialDetail['name'];
				$special_text =  $specialDetail['content'];
				$owner_id = $specialDetail['owner'];
				$checksucess = $lang->javascript->global->special->checksucess;
				$checkrefuse = $lang->javascript->global->special->checkrefuse;
				$specialovertm = $lang->javascript->global->special->overtm;
				$sendmail = 0;
				$where = "nid=".$nid;
				if($doing==2){
					$sql .=",checkinfo='".$checkinfo."'";		
					$checkrefuse = 	str_replace('{POINAME}',$poi_name,$checkrefuse);
					$checkrefuse = 	str_replace('{TEXT}',$special_text,$checkrefuse);
					$checkrefuse = 	str_replace('{RESON}',$checkinfo,$checkrefuse);		
					$content = 	$checkrefuse;	
					$sendmail =1;
				} else if($doing==1){
					$checksucess = 	str_replace('{POINAME}',$poi_name,$checksucess);
					$content = 	$checksucess;
					$sendmail = 1;
				} else if($doing==4){
					$where .= " "; 
				} else if($doing==5){
					$specialovertm = str_replace('{POINAME}',$poi_name,$specialovertm);
					$sql .=",checkinfo='".$specialovertm."'";							
					$content = 	$checkinfo;	
					$sendmail =1;				
				}
				$owner = split('\,',$owner_id);
		 		if(!is_array($owner)){
		 				$sendmailonce && $sendmail && Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, (int)$owner);						
		 		} else {
					foreach($owner as $row){					
						$sendmailonce && $sendmail && Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, (int)$row);						
					}	
		 		}				
				$select = "update ".BETTER_DB_TBL_PREFIX."poi_notification ".$sql." where ".$where;				
				$rs = self::squery($select,$this->rdb);
				$sendmailonce = 0;
			}
			
			//$row = $rs->fetchAll();	
			//Zend_Debug::dump($row);
			return $rs;
			
		}

    public function approve($poi_id)
    {
      $this->db->delete('better_poi_newly', 'poi_id = '.$poi_id);
      $this->update(array('level_adjust' => 20), array('poi_id'=>$poi_id, 'level_adjust'=>0));
    }
}
?>
