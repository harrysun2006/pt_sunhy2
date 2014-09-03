<?php

/**
 * POI详细页
 *
 * @package Controllers
 * @author yangl
 */

class PoiController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
		//$this->needLogin();
    	$this->commonMeta();

		$this->view->myfollowing = $this->uid ? $this->user->follow()->getFollowings() : array();
		$this->view->myblocking = $this->uid ? Better_User_Block::getInstance($this->uid)->getBlocks() : array();

		$this->view->headScript()->prependScript('
		betterUser.blocks = '.Better_Functions::toJsArray($this->view->myblocking).';'
		);
   		
   		
   		$this->view->shout_title = $this->lang->global->tips->title;
   		$this->view->shout_type = 'tips';
   		$this->view->shout_text = $this->lang->global->tips->text;

   		$this->view->needCheckinJs = true;
	}
	
	public function __call($method, $params)
	{
		$params = $this->getRequest()->getParams();
		$poiId = $params['action'];
		$this->indexAction($poiId);
		$this->render('index');
	}	
	
	public function indexAction($poiId=0)
	{
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/poi.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
		$userInfo = $this->user->getUser();
		$lon = $userInfo['lon'];
		$lat = $userInfo['lat'];
		$w = 5000;
		$h = 5000;

		$poiId = $poiId ? $poiId : Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));
		$bid = $this->getRequest()->getParam('bid',"0");
		if($bid){
			//通过新浪微博等同步的地方查看POI
			list($blog_uid, $i) = explode('.', $bid);
			$blog_user = Better_DAO_User::getInstance($blog_uid)->get($blog_uid);
			$blog_user=	Better_User::getInstance()->parseUser($blog_user, false, false, true);
			$this->view->bloguser = $blog_user;
			$this->view->currentUid = $this->uid;
		}

		if(!$poiId){
			$this->needLogin();			
		}
		
//		if (BETTER_4SQ_POI && $poiId && !is_numeric($poiId) && !strpos('-', $poiId)) {
//			$poiId = Better_Service_4sq_Pool::foursq2our($poiId);
//		}
		if (BETTER_AIBANG_POI && $poiId && !is_numeric($poiId)) {
			$poiId = Better_Service_Aibang_Pool::ab2our($poiId);
		}
		
		$poiDetail = array();

		if ($poiId>0) {
			$poiInfo = Better_Poi_Info::getInstance($poiId);
			$poiDetail = $poiInfo->get();
			if($poiDetail['closed']==1 && intval($poiDetail['ref_id'])>0) {
				$poiInfo = Better_Poi_Info::getInstance($poiDetail['ref_id']);
				$poiDetail = $poiInfo->get();
				$poiId = $poiDetail['poi_id'];
			}
		} else if ($poiId==0 && $this->userInfo['last_checkin_poi']) {
			$lastCheckinPoi = $this->userInfo['last_checkin_poi'];
			$poiInfo = Better_Poi_Info::getInstance($lastCheckinPoi);
			$poiDetail = $poiInfo->get();			
		} else {
			$result = Better_Search::factory(array(
				'what' => 'poi',
				'page' => 1,
				'count' => 1,
				'order' => 'checkins'
				))->search();			
				
			$poiId = $result['rows'][0]['poi_id'];
			$poiInfo = Better_Poi_Info::getInstance($poiId);
			$poiDetail = $poiInfo->get();
		}
		
		if (!$poiDetail['poi_id']) {
			throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
		}
		
		if ($this->uid) {
			$favPois = Better_User_PoiFavorites::getInstance($this->uid)->getFavorites();
			$this->view->favorited = in_array($poiDetail['poi_id'], $favPois) ? true : false;
			
			$this->view->headScript()->prependScript('
				betterUser.poi_favorites = '.json_encode($favPois).';
				');
		} else {
			$this->view->favorited = 0;
		}
		
		if($poiDetail['closed']==1){
			$this->view->poi_closed = 1;			
		}else{
			
			$this->view->inPoi = true;
			$poiDetail['creator_detail'] = Better_Output::filterUser($poiDetail['creator_detail']);
			$poiDetail['major_detail'] = Better_Output::filterUser($poiDetail['major_detail']);
			$poiDetail = Better_Output::filterPoi($poiDetail);
			
			$this->view->headScript()->prependScript('
			var Better_Poi_Id =\''.$poiDetail['poi_id'].'\';
			var Better_Nickname=\''.$userInfo['nickname'].'\';
			var Better_Poi_Detail = '.json_encode($poiDetail).';
			pageLon = '.(float)$poiDetail['lon'].';
			pageLat = '.(float)$poiDetail['lat'].';
			var Better_Poi_Favorited = '.(int)$this->view->favorited.';
				');
			$this->view->poiInfo = $poiDetail;
		   
			if ($poiDetail['poi_id']) {
				//签到排行榜
				$params['type']="checkin";
				$now = time();
				$min = $now-60*24*3600;//查看最近60天在该POI签到的用户
				$params['timestart']=$min;
				$params['timeend'] = $now;
				$result = Better_User_PlaceLog::getInstance($this->uid)->users($poiDetail['poi_id'],6,$params);		
				$this->view->aroundUsers = $result['rows'];
				//取得我在这一POI签到的次数和最近一次签到的时间
				if($this->uid>0){
					$lastCheckin = Better_DAO_Blog::getInstance($this->uid)->getUserLastCheckin($this->uid,$poiDetail['poi_id']);	
					$lastCheckinTime = $lastCheckin['dateline'];
					 $timediff = $now-$lastCheckinTime;
	     			$days = intval($timediff/86400);
	     			$remain = $timediff%86400;
	    			$hours = intval($remain/3600);
	    		 	$remain = $remain%3600;
	     			$mins = intval($remain/60);
	     			$deltaTime='';
	     			if($days>0){
	     				$deltaTime = $days.' 天前';
	     			}elseif ($hours>0){
	     				$deltaTime = $hours.' 小时前';
	     			}elseif ($mins>2){
	     				$deltaTime = $mins.' 分钟前';
	     			}else{
	     				$deltaTime = '刚刚签到';
	     			}
	     			$this->view->deltaTime = $deltaTime;
					$this->view->checkinCount = Better_DAO_Blog::getInstance($this->uid)->getUserCheckinCount($this->uid,$poiDetail['poi_id']);
				}
				//取得周围的相关的POI
				$xml = Better_Similarity::getSimilarityPois($poiDetail['poi_id']);
				$count=1;
				$relatedPois =  array();
				foreach ($xml->item as $item){
					$poiinfoa = Better_DAO_Poi_Info::getInstance()->getPoi($item['pid']);
					if(isset($poiinfoa['closed']) && $poiinfoa['closed']==0){
						$relatedPois[] =  $poiinfoa;
						$count++;
					}
					if($count>5){
						break;//只要显5条记录就可以了
					}
				}
				$this->view->relatedPois =  $relatedPois;
				
				//来过的好友
				$userObj = $this->uid ? $this->user : Better_User::getInstance();
				if($this->uid>0){
					$friendshere = Better_User_PlaceLog::getInstance($this->uid)->friends($poiDetail['poi_id'],1,20,$params);		
					$this->view->friendsHere = $friendshere['rows'];
					unset($friendshere);
				}
				//想来的 用户						
				$todoUsers = Better_Poi_Todo::getInstance(Better_Poi_Info::dehashId($poiDetail['poi_id']))->users(1, 14, false, array());
				$this->view->todoUsers =  $todoUsers['rows'];	
				unset($todoUsers)	;		
				//想来的好友
				if($this->uid>0){
					$data = $userObj->status()->getFriendstodo(array(
					'type'=>"todo",
					'poi' => $poiId,
					'page' => 1,
					'page_size' => 20
					));
					$todoFriends = $data['rows'];
					unset($data);
					$this->view->todoFriends =  $todoFriends;	
				}
				list($ca,$cb)=Better_Functions::conputeDisplayNum(count($this->view->friendsHere),count($this->view->todoFriends));
				$this->view->fcounthere = count($this->view->friendsHere);
				$this->view->fcounttodo = count($this->view->todoFriends);
				if(count($this->view->friendsHere)>$ca){
					$this->view->heremoreshow = true; 
					$countarr = Better_Poi_Checkin::getInstance($poiDetail['poi_id'])->countLog(array('uid'=>$this->uid));
					$this->view->fcounthere = $countarr['friend'];
					$this->view->friendsHere = array_slice($this->view->friendsHere,0,$ca);
				}
				if(count($this->view->todoFriends)>$cb){
					$this->view->todomoreshow = true; 
					$todocount = Better_Poi_Todo::getInstance($poiDetail['poi_id'])->count(array('uid'=>$this->uid));
					$this->view->fcounttodo = $todocount['friend'];
					$this->view->todoFriends = array_slice($this->view->todoFriends,0,$cb);
				}
				$this->view->displayNums=array('friendsHere'=>$ca,'todoFriends'=>$cb);
				if($this->uid>0){
					$this->view->bid= Better_DAO_Todo::getInstance($this->uid)->getBidByPoi($this->uid,$poiDetail['poi_id']);
				}
			}
			
			$major_checkin_counts = $poiDetail['major'] ? Better_DAO_User_PlaceLog::getInstance($poiDetail['major'])->getMyValidCheckinCount($poiDetail['poi_id']) : 0;
			$this->view->major_checkin_counts = $major_checkin_counts;
		}
		$isowner = 0;
		//将系统设置的管理员账号添加到店主列表中
		$owner = Better_Config::getAppConfig()->poi->defaultowner;
		if($poiDetail['ownerid']){			
			$temppoiowner = split('\,',$poiDetail['ownerid']);
			if($temppoiowner[0]>0){	
				
				$this->view->poiowerdetail = Better_User::getInstance($temppoiowner[0])->getUserInfo();
			}
			
			$owner .=",".implode(",",$temppoiowner);
		}
		$poiowner = split('\,',$owner);
		if(in_array($userInfo['uid'],$poiowner))
		{
			$isowner = 1;
		}
		
		$this->view->isowner = $isowner;
		$specialdate = array(
			'poi_id' => $poiId,
			'checked' => 1
		);
		$this->view->special = Better_Poi_Notification::getInstance($poiId)->getPoispecial($specialdate);
		$specialTitle = $this->view->special['total']>0?$this->view->special['rows'][0]['content']:'';
		$brIndex = mb_strpos($specialTitle,'<br',0,'utf-8');
		if($brIndex>0 && $brIndex<40){
			$specialTitle= mb_substr($specialTitle,0,$brIndex,'utf-8');
		}else{
			$specialTitle= mb_substr($specialTitle,0,40,'utf-8')."...";
		}
		$this->view->specialtitle=$specialTitle;
	}	
	
	public function viewuserlistAction()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/viewuser.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
		$userInfo = $this->user->getUser();
		$lon = $userInfo['lon'];
		$lat = $userInfo['lat'];
		$w = 5000;
		$h = 5000;

		$poiId = $poiId ? $poiId : Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));
		
		if(!$poiId){
			$this->needLogin();			
		}

		if (BETTER_AIBANG_POI && $poiId && !is_numeric($poiId)) {
			$poiId = Better_Service_Aibang_Pool::ab2our($poiId);
		}
		
		$poiDetail = array();

		$poiInfo = Better_Poi_Info::getInstance($poiId);
		$poiDetail = $poiInfo->get();
		if($poiDetail['closed']==1 && intval($poiDetail['ref_id'])>0) {
			$poiInfo = Better_Poi_Info::getInstance($poiDetail['ref_id']);
			$poiDetail = $poiInfo->get();
			$poiId = $poiDetail['poi_id'];
		}		
		if (!$poiDetail['poi_id']) {
			throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
		}
		
		if ($this->uid) {
			$favPois = Better_User_PoiFavorites::getInstance($this->uid)->getFavorites();
			$this->view->favorited = in_array($poiDetail['poi_id'], $favPois) ? true : false;
			
			$this->view->headScript()->prependScript('
				betterUser.poi_favorites = '.json_encode($favPois).';
				');
		} else {
			$this->view->favorited = 0;
		}
		
		if($poiDetail['closed']==1){
			$this->view->poi_closed = 1;			
		}else{
			
			$this->view->inPoi = true;
			$poiDetail['creator_detail'] = Better_Output::filterUser($poiDetail['creator_detail']);
			$poiDetail['major_detail'] = Better_Output::filterUser($poiDetail['major_detail']);
			$poiDetail = Better_Output::filterPoi($poiDetail);
			
			$this->view->headScript()->prependScript('
			var Better_Poi_Id =\''.$poiDetail['poi_id'].'\';
			var Better_Nickname=\''.$userInfo['nickname'].'\';
			var Better_Poi_Detail = '.json_encode($poiDetail).';
			pageLon = '.(float)$poiDetail['lon'].';
			pageLat = '.(float)$poiDetail['lat'].';
			var Better_Poi_Favorited = '.(int)$this->view->favorited.';
				');
			$this->view->poiInfo = $poiDetail;
		   
			if ($poiDetail['poi_id']) {
				//签到排行榜
				$params['type']="checkin";
				$now = time();
				$min = $now-60*24*3600;//查看最近60天在该POI签到的用户
				$params['timestart']=$min;
				$params['timeend'] = $now;
				$result = Better_User_PlaceLog::getInstance($this->uid)->users($poiDetail['poi_id'],6,$params);		
				$this->view->aroundUsers = $result['rows'];
				//取得我在这一POI签到的次数和最近一次签到的时间
				if($this->uid>0){
					$lastCheckin= Better_DAO_Blog::getInstance($this->uid)->getUserLastCheckin($this->uid,$poiDetail['poi_id']);			
					$lastCheckinTime = $lastCheckin['dateline'];
					 $timediff = $now-$lastCheckinTime;
	     			$days = intval($timediff/86400);
	     			$remain = $timediff%86400;
	    			$hours = intval($remain/3600);
	    		 	$remain = $remain%3600;
	     			$mins = intval($remain/60);
	     			$deltaTime='';
	     			if($days>0){
	     				$deltaTime = $days.' 天前';
	     			}elseif ($hours>0){
	     				$deltaTime = $hours.' 小时前';
	     			}elseif ($mins>2){
	     				$deltaTime = $mins.' 分钟前';
	     			}else{
	     				$deltaTime = '刚刚签到';
	     			}
	     			$this->view->deltaTime = $deltaTime;
					$this->view->checkinCount = Better_DAO_Blog::getInstance($this->uid)->getUserCheckinCount($this->uid,$poiDetail['poi_id']);
				}
				//取得周围的相关的POI
				$xml = Better_Similarity::getSimilarityPois($poiDetail['poi_id']);
				$count=1;
				$relatedPois =  array();
				foreach ($xml->item as $item){
					$poiinfoa = Better_DAO_Poi_Info::getInstance()->getPoi($item['pid']);
					if(isset($poiinfoa['closed']) && $poiinfoa['closed']==0){
						$relatedPois[] =  $poiinfoa;
						$count++;
					}
					if($count>5){
						break;//只要显5条记录就可以了
					}
				}
				$this->view->relatedPois =  $relatedPois;
				
				//来过的好友 
				if($this->uid>0){//判断查看列表的类型				
					$this->view->viewtype =$this->getRequest()->getParam('type', 'friendshere');						
				}
			}			
			$major_checkin_counts = $poiDetail['major'] ? Better_DAO_User_PlaceLog::getInstance($poiDetail['major'])->getMyValidCheckinCount($poiDetail['poi_id']) : 0;
			$this->view->major_checkin_counts = $major_checkin_counts;
		}
		$isowner = 0;
		//将系统设置的管理员账号添加到店主列表中
		$owner = Better_Config::getAppConfig()->poi->defaultowner;
		if($poiDetail['ownerid']){			
			$temppoiowner = split('\,',$poiDetail['ownerid']);
			if($temppoiowner[0]>0){	
				
				$this->view->poiowerdetail = Better_User::getInstance($temppoiowner[0])->getUserInfo();
			}
			
			$owner .=",".implode(",",$temppoiowner);
		}
		$poiowner = split('\,',$owner);
		if(in_array($userInfo['uid'],$poiowner))
		{
			$isowner = 1;
		}
		
		$this->view->isowner = $isowner;	
	}
	
	public function ownerAction()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/poi/owner.js?ver='.BETTER_VER_CODE);	
			
		$userInfo = $this->user->getUser();
		
		$lon = $userInfo['lon'];
		$lat = $userInfo['lat'];
		$w = 5000;
		$h = 5000;

		$poiId = Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));		
		if(!$userInfo['uid']){
			$this->needLogin();			
		}
		
//		if (BETTER_4SQ_POI && $poiId && !is_numeric($poiId) && !strpos('-', $poiId)) {
//			$poiId = Better_Service_4sq_Pool::foursq2our($poiId);
//		}
		if (BETTER_AIBANG_POI && $poiId && !is_numeric($poiId)) {
			$poiId = Better_Service_Aibang_Pool::ab2our($poiId);
		}
		
		$poiDetail = array();
		$begtm = gmmktime(16,0,0,date("m"),date("d")-31,date("Y"));
		$endtm = gmmktime(16,0,0,date("m"),date("d")-1,date("Y"));
		$this->view->begtm = date("Y-m-d",$begtm);
		$this->view->endtm = date("Y-m-d",$endtm);
		
		
		if ($poiId>0) {
			$poiInfo = Better_Poi_Info::getInstance($poiId);
			$poiDetail = $poiInfo->get();
			//将系统设置的管理员账号添加到店主列表中
			$owner = Better_Config::getAppConfig()->poi->defaultowner;
			if($poiDetail['ownerid']){
				$owner .=",".$poiDetail['ownerid'];
			}
			$poiowner = split('\,',$owner);
			if(!in_array($userInfo['uid'],$poiowner))
			{			
				$this->_helper->getHelper('Redirector')->gotoUrl('/poi/'.$poiId);
			}
			$specialdate = array(
				'poi_id' => $poiId,
				'checked' => '0,1'
			);
			$this->view->special = Better_Poi_Notification::getInstance($poiId)->getPoispecial($specialdate);				
		}		
		
		if($poiDetail['closed']==1){
			$this->view->poi_closed = 1;			
		}else{
			$this->view->inPoi = true;
			$this->view->headScript()->prependScript('
			var Better_Poi_Id =\''.$poiDetail['poi_id'].'\';
			var Better_Poi_Detail = '.json_encode($poiDetail).';
			pageLon = '.(float)$poiDetail['lon'].';
			pageLat = '.(float)$poiDetail['lat'].';
				');
			$this->view->poiInfo = $poiDetail;		
			//获得最近签到的12位
			$this->view->lastcheckin = Better_Poi_Checkin::getInstance(Better_Poi_Info::dehashId($poiDetail['poi_id']))->lastcheckin();			
					
			$this->view->topcheckin = Better_Poi_Checkin::getInstance(Better_Poi_Info::dehashId($poiDetail['poi_id']))->topcheckin();//签到最多的3人	
		
			$this->view->checkintimes = $this->view->topcheckin['total'];	//总的签到人数
			/*
			$checkinhour = Better_Poi_Checkin::getInstance(Better_Poi_Info::dehashId($poiDetail['poi_id']))->checkinhour();
		
			$checkinhours = array(array('timehour'=>'04,05,06,07,08,09','times'=>'0','time_interval'=>$this->lang->global->poi->owner->morning),array('timehour'=>'10,11,12,13','times'=>'0','time_interval'=>$this->lang->global->poi->owner->noon),array('timehour'=>'14,15,16','times'=>'0','time_interval'=>$this->lang->global->poi->owner->afternoon),array('timehour'=>'17,18,19,20,21,22','times'=>'0','time_interval'=>$this->lang->global->poi->owner->night),array('timehour'=>'23,00,01,02,03','times'=>'0','time_interval'=>$this->lang->global->poi->owner->midnight));	
			$this->view->totalcheckinhours = 0; // 各时段的签到总次数
			for($i=0;$i<$checkinhour['total'];$i++){
				$timehour = array();
				for($j=0;$j<count($checkinhours);$j++){
					$timehour = split(',',$checkinhours[$j]['timehour']);
													
					if(in_array($checkinhour['rows'][$i]['hour'],$timehour)){
						$checkinhours[$j]['times'] = $checkinhours[$j]['times']+$checkinhour['rows'][$i]['checkintimes'];						
					}
				}
				$totalcheckinhours = $totalcheckinhours+$checkinhour['rows'][$i]['checkintimes'];
			}			
			$this->view->checkinhours = $checkinhours; //各时段的签到数据
			$this->view->totalcheckinhours = $totalcheckinhours;
			
			$checkinday = Better_Poi_Checkin::getInstance(Better_Poi_Info::dehashId($poiDetail['poi_id']))->checkinday();	
			
			$checkin_date = array();
			$checkin_num = array();
			for($i=0;$i<30;$i++){
				$tmpdays =  date('n-j',mktime(0, 0, 0, date("m"),date("d")-30+$i));				
				$checkin_num[] = 0;
				$checkin_date[] = $tmpdays;				
				//Zend_Debug::dump($checkinday);
				foreach($checkinday['rows'] as $rows){
					if($rows['days']==$checkin_date[$i]){
						$checkin_num[$i] = $checkin_num[$i]+$rows['checkintimes'];
					}				
				}				
			}
			for($i=0;$i<count($checkin_date);$i++){
				if($i!=0 && $i!=(count($checkin_date)-1) && ($i%6!=0)){
					$checkin_date[$i] = '&nbsp;';
				}
			}	
			//Zend_Debug::dump($checkin_num);
			$this->view->checkin_date = json_encode($checkin_date);//前30天的日期
		//	$this->view->checkin_num = json_encode($checkin_num);//前30天的签到数据			
		
			$checkingender = Better_Poi_Checkin::getInstance(Better_Poi_Info::dehashId($poiDetail['poi_id']))->checkingender();			
			$checkin_genderdate = array(array('male',0),array('female',0),array('secret',0));
			
			
			$totalgender= 0;
			for($i=0;$i<count($checkin_genderdate);$i++){
				$tmpgender = $checkin_genderdate[$i][0]; 						
				foreach($checkingender['rows'] as $rows){					
					if($rows['gender']==$tmpgender){
						$checkin_genderdate[$i][1] = $checkin_genderdate[$i][1]+$rows['checkintimes'];
						$totalgender +=$rows['checkintimes'];
					}	
				}									
			}
			
			$this->view->checkintimes = $totalgender;
			
			for($i=0;$i<count($checkin_genderdate);$i++){	
							
				$checkin_genderdate[$i][1] =$totalgender>0 ? round($checkin_genderdate[$i][1]/$totalgender,2)*100 : 0;
				switch ($checkin_genderdate[$i][0]){
					case 'male':
						$checkin_genderdate[$i][0] =$this->lang->global->poi->owner->male;
					break;
					case 'female':
						$checkin_genderdate[$i][0] =$this->lang->global->poi->owner->female;
					break;
					case 'secret':
						$checkin_genderdate[$i][0] =$this->lang->global->poi->owner->secret;
					break;
				}
			}	
			//$this->view->checkin_genderdate = json_encode($checkin_genderdate);
			
			
			$supportedProtocols = Better_Service_PushToOtherSites::$openingProtocols;		
			$synclist = Better_Poi_Info::getInstance(Better_Poi_Info::dehashId($poiDetail['poi_id']))->getsync();	
			$sync_checkin = array();
			$sync_tips = array();
			$max_checkin = 0;
			$max_tips = 0;
			$syncsite = Better_Service_PushToOtherSites::$shortProtocols;	
			for($i=0;$i<count($supportedProtocols);$i++){
				$protocol = $supportedProtocols[$i];						
				$sync_checkin[$i] = 0;
				$sync_tips[$i] = 0;
				for($j=0;$j<count($synclist);$j++){
					if($synclist[$j]['protocol']==$protocol && $synclist[$j]['type']=='checkin'){
						$max_checkin = $max_checkin>$synclist[$j]['number'] ? $max_checkin:$synclist[$j]['number'];
						$sync_checkin[$i]=(int)$synclist[$j]['number'];					
					}
					
				}
			}		
		
			$this->view->synclist = json_encode($syncsite);
			$this->view->sync_tips = json_encode($sync_tips);		
			$this->view->sync_checkin = json_encode($sync_checkin);
			*/
		}
		
	}		
	
	public function newspecialAction()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/poi/special.js?ver='.BETTER_VER_CODE);	
			
		$userInfo = $this->user->getUser();
		
		$lon = $userInfo['lon'];
		$lat = $userInfo['lat'];
		$w = 5000;
		$h = 5000;

		$poiId = Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));		
		if(!$userInfo['uid']){
			$this->needLogin();			
		}
		
		if (BETTER_AIBANG_POI && $poiId && !is_numeric($poiId)) {
			$poiId = Better_Service_Aibang_Pool::ab2our($poiId);
		}
		
		$poiDetail = array();
		$begtm = gmmktime(16,0,0,date("m"),date("d")-31,date("Y"));
		$endtm = gmmktime(16,0,0,date("m"),date("d")-1,date("Y"));
		$this->view->begtm = date("Y-m-d",$begtm);
		$this->view->endtm = date("Y-m-d",$endtm);
		
		
		if ($poiId>0) {
			$poiInfo = Better_Poi_Info::getInstance($poiId);
			$poiDetail = $poiInfo->get();
			//将系统设置的管理员账号添加到店主列表中
			$owner = Better_Config::getAppConfig()->poi->defaultowner;
			if($poiDetail['ownerid']){
				$owner .=",".$poiDetail['ownerid'];
			}
			$poiowner = split('\,',$owner);
			if(!in_array($userInfo['uid'],$poiowner))
			{			
				$this->_helper->getHelper('Redirector')->gotoUrl('/poi/'.$poiId);
			}
			$specialdate = array(
				'poi_id' => $poiId,
				'checked' => '0,1'
			);
			$this->view->special = Better_Poi_Notification::getInstance($poiId)->getPoispecial($specialdate);				
		}		
		
		if($poiDetail['closed']==1){
			$this->view->poi_closed = 1;			
		}else{
			$this->view->inPoi = true;
			$this->view->headScript()->prependScript('
			var Better_Poi_Id =\''.$poiDetail['poi_id'].'\';
			var Better_Poi_Detail = '.json_encode($poiDetail).';
			pageLon = '.(float)$poiDetail['lon'].';
			pageLat = '.(float)$poiDetail['lat'].';
				');
			$this->view->poiInfo = $poiDetail;	
		}
		$this->view->lastcheckin = Better_Poi_Checkin::getInstance(Better_Poi_Info::dehashId($poiDetail['poi_id']))->lastcheckin();	
	}
	public function editspecialAction(){
		
	}
}