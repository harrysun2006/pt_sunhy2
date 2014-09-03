<?php
/**
 * admin POI controller
 * @author yangl
 */
class Admin_PoiController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/poi.js?ver='.BETTER_VER_CODE);
		
		$this->view->title="POI管理";		
	}
	
	public function indexAction()
	{ 
		
		$params = $this->getRequest()->getParams();
		if(isset($params['doubt']) && $params['doubt']=='1'){
			exit(0);
		}
		$result = array();
		$result=Better_Admin_Poi::getPOIs($params);
		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	public function delAction(){
		
		$result=0;
		$params = $this->getRequest()->getParams();
		$poids=isset($params['poids']) ? $params['poids'] : '';
		Better_Admin_Poi::deletePOI($poids) && $result=1;
		$this->sendAjaxResult($result);
	}
	
	
	public function reopenAction(){
		$result = 0 ;
		$pois = $this->getRequest()->getParam('pois', '');
		
		if($pois && is_string($pois)){
			$pids = explode(',', $pois);
			
			if(count($pids)>0){
				foreach($pids as $pid){
					Better_DAO_Admin_Poi::getInstance()->update(array('ref_id'=>'', 'closed'=>0), $pid);
					Better_DAO_Poi_Fulltext::getInstance()->updateItem($pid, 1);
				}
				
				$result = 1;
			}
		}
		
		$this->sendAjaxResult($result);
		
	}
	
	
	public function mergeAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		$poi_id= $params['poi_id']? $params['poi_id']:'';
		$target_pid = $params['target_pid']? $params['target_pid']:'';
		
		if($poi_id && $target_pid){
			
			$result = Better_Admin_Simipoi::mergePOI($poi_id, $target_pid);
		}
		
		$this->sendAjaxResult($result);
	}
	
	public function updateAction(){
		
		$data=array();
		$result=0;
		$params = $this->getRequest()->getParams();
		$poi_id=isset($params['poi_id'])? $params['poi_id']:'';
		$category=isset($params['poi_cate'])? $params['poi_cate']:'';
		$name=isset($params['poi_name'])? $params['poi_name']:'';
		$city=isset($params['poi_city'])? $params['poi_city']:'';
		$address=isset($params['poi_address'])? $params['poi_address']:'';
		$tell=isset($params['poi_tell'])? $params['poi_tell']:'';
		//$major=isset($params['poi_major'])? $params['poi_major']:0;
		$lat=isset($params['poi_lat'])? $params['poi_lat']: 0;
		$lon=isset($params['poi_lon'])? $params['poi_lon']: 0;
		$label=isset($params['poi_label'])? $params['poi_label']: '';
		$intro=isset($params['poi_intro'])? $params['poi_intro']: '';
		$ownerid=isset($params['ownerid'])? $params['ownerid']: '';		
		$certified = isset($params['certified'])? $params['certified']: 0;
		$closed = isset($params['closed']) ? $params['closed'] : 0;
		$forbid_major = isset($params['forbid_major']) ? $params['forbid_major'] : 0;
		$level_adjust = isset($params['level']) ? $params['level'] : '';		
		$autochecked = isset($params['autochecked']) ? $params['autochecked'] : '';
		$autodmessage = isset($params['autodmessage']) ? $params['autodmessage'] : '';
		$denounce_id = isset($params['denounce_id']) ? $params['denounce_id'] : '';
		$denounce_uid = isset($params['denounce_uid']) ? $params['denounce_uid'] : '';
		Better_Log::getInstance()->logInfo('xx\n','update');
		if($poi_id && $category && $name && $lon && $lat){
			
			list($x, $y) = Better_Functions::LL2XY($lon, $lat);
			
			$data=array(
				'category_id'=>$category,
				'name'=>$name,
				'city'=>$city,
				'address'=>$address,
				'phone'=>$tell,
				'x'=>$x,
				'y'=>$y,
				'label'=>$label,
				'intro'=>$intro,				
				'certified'=>$certified,
				'closed'=>$closed,
				'forbid_major'=>$forbid_major,
				'level_adjust'=>$level_adjust,
				'ownerid'=> $ownerid
			);			
			
		}
		
		$poiInfo = Better_Poi_Info::getInstance($poi_id)->getBasic();
		if($poiInfo['poi_id'] && $ownerid && $poiInfo['ownerid']!=$ownerid){
			Better_Log::getInstance()->logInfo("店主：".$ownerid."POI:".$poi_id,'updatepoi');
			Better_Admin_Poi::updatePoiOwner($ownerid,$poi_id);			
		} else if($poiInfo['poi_id'] && $poiInfo['ownerid'] && !$ownerid) {
			Better_Admin_Poi::clearPoiOwner($poi_id);				
		}	
			
		if($poi_id && count($data)>0){		
			Better_Admin_Poi::updatePOI($data, $poi_id) && $result=1;	
			if($result){
//				if($autochecked && $denounce_id){
//					$status = 'have_progress';
//					//Better_Log::getInstance()->logInfo('status\n','update');
//					Better_DAO_Admin_Denouncepoi::getInstance()->changeStatus($denounce_id, $status);
//					if($autodmessage && $denounce_uid){
//						Better_Log::getInstance()->logInfo('message\n','update');
//						$user = Better_User::getInstance($uid);
//						$userInfo = $user->getUserInfo();		
//						$content = $user->getUserLang()->global->admin_denounce_thank;						
//						Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, $denounce_uid);	
//					}
//				}
				//如果采用的是用户的建议，那么发私信给举报用户，并且给用户的karma值加5
				//自动的把状态改为已处理
				if($autochecked && $denounce_id){
					$status = 'have_progress';
					Better_Log::getInstance()->logInfo('status\n','update');
					Better_DAO_Admin_Denouncepoi::getInstance()->changeStatus($denounce_id, $status);
				}
				if($autodmessage && $denounce_uid && $denounce_id){
						Better_Log::getInstance()->logInfo('message\n','update');
//						$user = Better_User::getInstance($denounce_uid);
//						$userInfo = $user->getUserInfo();		
						$params= array(
						   'rp'=>5,
						   'category'=>'passed_poi_update'
						);
						Better_User_Rp::getInstance($denounce_uid)->update($params);
						$content = "您举报的【".$name."】的资料不准的问题已经被确认了，获得了5个Karma。感谢您的热心奉献。";						
						Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, $denounce_uid);	
				}				
			}
		}
		
		$this->sendAjaxResult($result);
		
	}
	
	
	public function resetmajorAction(){
	
		$result =0;
		$params = $this->getRequest()->getParams();
		$poids=isset($params['poids']) ? $params['poids'] : array();
		Better_Admin_Poi::resetMajor($poids) && $result=1;
		$this->sendAjaxResult($result);
	}
	
	public function updatepoiAction(){
		
		$poiId = Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));
		$denounce_uid = $this->getRequest()->getParam('denounce_uid', '');
		$denounce_id = $this->getRequest()->getParam('denounce_id', '');
		$poiDetail = array();
		
		if ($poiId>0) {
			$poiInfo = Better_Poi_Info::getInstance($poiId);
			$poiDetail = $poiInfo->get();
		}	
	
		$this->view->headScript()->prependScript('
		var Better_Poi_Id =\''.$poiDetail['poi_id'].'\';
		var Better_Poi_Detail = '.json_encode($poiDetail).';
		');
		
		$poicategories=Better_Admin_Poi::getPOICategory();
		$this->view->categories = $poicategories;

		$this->view->poiDetail = $poiDetail;
		$this->view->denounce_id = $denounce_id;
		$this->view->denounce_uid = $denounce_uid;
	}
	
	
	public function searchAction(){
		
		$params = $this->getRequest()->getParams();
		
		$poi_id = $params['id']? $params['id'] : '';
		$poi = Better_Poi_Info::getInstance($poi_id)->getBasic();
		
		$params['keyword'] = $params['keyword']? $params['keyword'] : $poi['name'];		
		
		$lon = (float)$this->getRequest()->getParam('lon', $poi['lon']);
		$lat = (float)$this->getRequest()->getParam('lat', $poi['lat']);
		$range = (int)$this->getRequest()->getParam('range', 5000);
		$keyword = trim($this->getRequest()->getParam('keyword', $poi['name']));
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$order = trim($this->getRequest()->getParam('order', ''));
		$page = $this->getRequest()->getParam('page', 1);
		$certified = (bool)($this->getRequest()->getParam('certified', 'false')=='false' ? false : true);
		$category = (int)$this->getRequest()->getParam('category', 0);
		
		$poiParams = array(
			'what' => 'poi',
			'lon' => $lon,
			'lat' => $lat,
			'range' => $range,
			'certified' => $certified,
			'keyword' => $keyword,
			'order' => 'distance',
			'category' => $category,
			'page' => $page,
			'count' => $count,
		);	
		if ($this->ft()) {
			$poiParams['method'] = 'fulltext';
		}
		$rows = Better_Search::factory($poiParams)->search();
		if (count($rows['rows'])==0) {
			$newPoiParams = $poiParams;
			$newPoiParams['keyword'] = 'more:('.$keyword.')';
			$rows = Better_Search::factory($newPoiParams)->search();
		}
		
		$total = $rows['total'];
		
		$newrows = array();
		//$jointhis = 0;
		foreach($rows['rows'] as $k=>$row){
			if($poi_id==$row['poi_id']){
				unset($row[$k]);
				$total -= 1;
			}else{
				$newrows[]= $row;
			//	$jointhis = 1;
			}
		}
		unset($rows);
		/*
		if($jointhis){
			array_unshift($newrows,$poi);
		}
		*/
		$this->view->poi_info = $poi;
		$this->view->params = $params;
		$this->view->rows = $newrows;
		$this->view->count = $total;
	}
	
	public function refAction(){
		
		$result = 0;
		$params = $this->getRequest()->getParams();
		Better_Admin_Poi::refPOI($params) && $result = 1;
		
		$this->sendAjaxResult($result);		
	}
	
	public function addAction(){
		$params = $this->getRequest()->getParams();	
			
		if($params['create']){
			$name = trim(urldecode($this->getRequest()->getParam('poi_name', '')));
			$poi_ll = trim($this->getRequest()->getParam('poi_ll', ''));
			$lon = (float)$this->getRequest()->getParam('poi_lon', 0);
			$lat = (float)$this->getRequest()->getParam('poi_lat', 0);
			$phone = trim($this->getRequest()->getParam('poi_tell', ''));
			$category = (int)$this->getRequest()->getParam('poi_cate', 0);
			$address = trim(urldecode($this->getRequest()->getParam('poi_address', '')));
			$city = trim(urldecode($this->getRequest()->getParam('poi_city', '')));
			$province = trim(urldecode($this->getRequest()->getParam('poi_province', '')));
			$country = trim(urldecode($this->getRequest()->getParam('poi_country', '')));
			$level = trim(urldecode($this->getRequest()->getParam('poi_level', 99)));
			$label= trim(urldecode($this->getRequest()->getParam('poi_label', '')));
			
			if($poi_ll){
				$tmp = explode(',', $poi_ll);
				$lon = (float)$tmp[1];
				$lat = (float)$tmp[0];
			}
			
			$result = Better_Poi_Info::create(array(
				'name' => $name,
				'lon' => $lon,
				'lat' => $lat,
				'phone' => $phone,
				'category_id' => $category,
				'address' => $address,
				'country' => $country,
				'city' => $city,
				'province' => $province,
				'creator' => 1,
				'level'=> $level,
				'label'=> $label
				));
			
			if($result['code']==1){
				$this->view->message = '创建成功';
			}else{
				$this->view->message = $result['code'];
			}
		}else{
			$poicategories=Better_Admin_Poi::getPOICategory();
			$this->view->categories = $poicategories;
			$this->view->message = '';
			$this->view->params = $params;
		}
	}
	
	public function getpoisAction()
	{
		//get all pois
		$results = array();
		
		$params = $this->getRequest()->getParams();
		$page = $params['page'];
		$seach_mode = $params['seach_mode']? $params['seach_mode']:'';
		if($seach_mode){
			$poi_id= $params['poi_id']? $params['poi_id']:'';
			$from = $params['from']?strtotime($params['from']):0;
			$to = $params['to']?strtotime($params['to']):time();
			$page_size =$params['page_size']?$params['page_size']:BETTER_PAGE_SIZE;
			$sets = array(
			"poi_id"=>$poi_id,
			"from" =>$from,
			"to" =>$to,
			"page"=>$page,
			"page_size"=>$page_size
			);
			$results = Better_Admin_Poi::getAllpoisBySearch($sets);
		}else{
			$results = Better_Admin_Poi::getAllpoisUpdatedbyuser($page);
		}
		$count = $results['count'];		
		$rows = array();
		foreach ($results['rows'] as $key=>$result){
			$result['change_content'] = $result['change_content']? json_decode($result['change_content']):array();
			$old=$result['change_content']->old;
			$new = $result['change_content']->new;
			$contentString = '';
			if($new->name && $new->name != $old->name){
				$contentString .='<i>名称</i>: '.$old->name.' --> '.$new->name.'<br \>';
			}
			if($new->address && $new->address != $old->address){
				$contentString .='<i>地址</i>: '.$old->address.' --> '.$new->address.'<br \>';
			}
			if($new->city && $new->city != $old->city){
				$contentString .='<i>城市</i>: '.$old->city.' --> '.$new->city.'<br \>';
			}
			if($new->phone && $new->phone != $old->phone){
				$contentString .='<i>电话</i>: '.$old->phone.' --> '.$new->phone.'<br \>';
			}
			if($new->category && $new->category != $old->category){
				//根据id取得分类的名称
				$newName = Better_Admin_Poi::getNameByCategroyId($new->category)?Better_Admin_Poi::getNameByCategroyId($new->category):'没有此分类';
				$oldName = Better_Admin_Poi::getNameByCategroyId($old->category)?Better_Admin_Poi::getNameByCategroyId($old->category):'没有此分类';
				$contentString .='<i>分类</i>: '.$oldName.' --> '.$newName.'<br \>';
			}
			if(($new->lon && $new->lon != $old->lon) || ($new->lat && $new->lat != $old->lat)){
				$contentString .='<i>经纬度</i>: '."($old->lon,$old->lat)".' --> '."($new->lon,$new->lat)";
			}
			$result['change_content'] =  $contentString;
			$check='';
			switch($result['checked']){
				case 0: $check='未审核';break;
				case 1: $check='审核通过';break;
				case 2: $check='审核未通过';break;
			}
			$result['checked']=$check;
			$results['rows'][$key] = $result;
		}
		$this->view->rows = $results['rows'];		
		$this->view->count = $count;
		$this->view->params = $params;
	}
	
	
	public function checkpoiAction()
	{
		$params = $this->getRequest()->getParams();
		$check_id= $params['check_id']? $params['check_id']:'';
		
		$result = Better_Admin_Poi::getUpdateById($check_id);
		$time = $result['dateline'];
		$changeContent = $result['change_content']?json_decode($result['change_content']):array();
		$old=$changeContent->old;
		$new = $changeContent->new;
		$uid = $result['uid'];
		
		$this->view->headScript()->prependScript('
		var Better_Poi_Detail = '.json_encode($old).';
		var Better_Poi_new_Detail = '.json_encode($new).';
		');
		switch($result['checked']){
				case 0: $checked='未审核';break;
				case 1: $checked='审核通过';break;
				case 2: $checked='审核未通过';break;
		}
		$this->view->poi_id = $result['poi_id'];
		$this->view->id = $result['id'];
		$this->view->old = $old;
		$this->view->new = $new;
		$this->view ->uid = $uid;
		$this->view->time = $time;
		$this->view->checked = $checked;
	}
	
	
	/**
	 * @param id 待审核id
	 * @param status 1:审核通过；2：审核不通过
	 */
	public function updatecheckpoiAction()
	{
		$result = 0; 
		$params = $this->getRequest()->getParams();
		$id= $params['id']? $params['id']:'';
		$status = $params['status']? $params['status']:'';
		if($id){
			$checkDetail = Better_Admin_Poi::getUpdateById($id);
			if($checkDetail['checked']=='1'){//需要确定是待审核状态
				$result=3;
			}else{
				$result = Better_Admin_Poi::updateCheckpoi($id,$status); // change the status of the check table
			}
			if($result==1 ||$result==2){			   
				$changeContent = $checkDetail['change_content']?json_decode($checkDetail['change_content']):array();
				$updateCheck = $changeContent->new;
				$updateCheck = (array)$updateCheck;
				$updateParams = array();
				foreach ($updateCheck as $key=>$value){
					if($key=='category'){
						$updateParams['category_id'] = $value;
					}elseif($key=='lon'){
						$lon = $value;
					}elseif ($key=='lat'){
						$lat = $value;
					}else{
						$updateParams[$key] = $value;
					}
				}
				
				$updateParams['poi_id']=$checkDetail['poi_id'];
				$poi = Better_Poi_Info::getInstance($updateParams['poi_id'])->getBasic();
				
				list($x, $y) =  Better_Functions::LL2XY($poi['lon'], $poi['lat']);
				$needupdate=false;//查看否有所改动，如果没有改动的话不需要更新数据直接返回即可
				foreach ($updateParams as $key=>$value){
					if($poi[$key] && $value != $poi[$key]){
						$needupdate = true;
						break;
					}
				}				
				if($lon && $lat){
					list($newX,$newY) = Better_Functions::LL2XY($lon,$lat);
					$distance = sqrt(pow($newX-$x,2)+pow($newY-$y,2));
					if($distance > 5 || $distance == 5){
						$updateParams['x']=$newX;
				  		$updateParams['y']=$newY;
				  		$needupdate=true;
					}					
				}				
			}
			if($result==1){	//更新POI
				if($needupdate){
					$flag = Better_Poi_Info::getInstance()->update($updateParams);
				}else{
					$flag = 1;
				}
				if($flag){
					$result=1;
				}else{
					$result=0;
				}	
			}
		}
		
		if($result==1){
			//审核通过后给用户增加 5点 Karma 
			$params= array(
			   'rp'=>5,
			   'category'=>'passed_poi_update'
			);
			Better_User_Rp::getInstance($checkDetail['uid'])->update($params);
			$poiName =$updateParams['name']?$updateParams['name']:$poi['name'];
			// send the secret message
			$content = "您修改的【".$poiName."】的资料已经被采用了，获得了5个Karma。感谢您的热心奉献。";
			$msg_id = Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, $checkDetail['uid']);
			//log
			if(Better_Registry::get('sess') && Better_Registry::get('sess')->admin_uid){
				$admin_uid = Better_Registry::get('sess')->admin_uid;
			}else{
				$admin_uid = '100';
			}
			$logmsg = '审核通过用户的更新POI的请求';	
			$logmsg .='<br> check id 为'.$id;
			Better_Admin_Administrators::getInstance($admin_uid)->addPoiLog($updateParams['poi_id'], 'update_poi_info', $logmsg);
		}elseif($result==2){
			//审核不通过时候发送相关私信和记录log
			$poiName =$updateParams['name']?$updateParams['name']:$poi['name'];
			$content = "您修改的【".$poiName."】的资料未能被审核通过，但是非常感谢您的热心奉献。";
			$msg_id = Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, $checkDetail['uid']);
			//log
			if(Better_Registry::get('sess') && Better_Registry::get('sess')->admin_uid){
				$admin_uid = Better_Registry::get('sess')->admin_uid;
			}else{
				$admin_uid = '100';
			}
			$logmsg = '审核不通过用户的更新POI的请求';	
			$logmsg .='<br> check id 为'.$id;
			Better_Admin_Administrators::getInstance($admin_uid)->addPoiLog($updateParams['poi_id'], 'update_poi_info', $logmsg);
		}
		$this->sendAjaxResult($result);
	}
	
	
	public function deletecheckpoiAction()
	{
		$result = 0; 
		$params = $this->getRequest()->getParams();
		$id= $params['id']? $params['id']:'';
		if($id){			
			$result = Better_Admin_Poi::deleteCheckpoi($id);
		}
		$this->sendAjaxResult($result);
	}

  // approve newly created POI
  public function approveAction()
  {
    $result = '';
		$params = $this->getRequest()->getParams();
    if(isset($params['id']))
    {
      if(is_array($params['id']))
      {
        foreach($params['id'] as $pid)
        {
          Better_DAO_Admin_Poi::getInstance()->approve($pid);
          $result .= $pid." done\n";
        }
      }
      else
      {
        $pid = $params['id'];
        Better_DAO_Admin_Poi::getInstance()->approve($pid);
        $result .= $pid." done\n";
      }
    }
		echo $result;
    exit(0);
  }
}

?>
