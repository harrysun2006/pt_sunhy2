<?php
/**
 * admin POI controller
 * @author yangl
 */
class Admin_ActivityController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();						
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/activity.js?ver='.BETTER_VER_CODE);
		$this->view->title="活动管理";		
	}	
	
	
	public function indexAction(){		
		$params = $this->getRequest()->getParams();		
		$result=Better_Activity::getAllactivity($params);			
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	
	}
	
	public function updateactivityAction(){
		$this->view->headScript()->appendFile($this->jsUrl.'/jquery/mixed.js?ver='.BETTER_VER_CODE);
		$nid = $this->getRequest()->getParam('id', '');
	
		$specialDetail = array();		
		$params = array(
			'act_id'  => $nid		
		);
		if ($nid>0) {
			$activity = Better_DAO_Activity::getInstance()->get($params);
			$activity_poi = Better_DAO_Activitypoi::getInstance()->getAll($params);
			$poi_str = '';
			$addstr = '';
			foreach($activity_poi as $row){
				$poi_str .=$addstr.$row['poi_id'];
				$addstr = ",";
			}
		}		
		$this->view->activity_poi = $poi_str;
		$this->view->activity = $activity;		
	}
	public function newactivityAction(){
		$this->view->headScript()->appendFile($this->jsUrl.'/jquery/mixed.js?ver='.BETTER_VER_CODE);		
		$this->view->maxid = Better_DAO_Activity::getInstance()->getMaxid()+1;			
	}
	public function doupdateactivityAction(){
		$result = 1;
		$params = $this->getRequest()->getParams();		
		$act_id = (int)$params['act_id'];
		$poi_id = $params['poi_str'];
		$content = trim($params['content']);
		$title = trim($params['title']);		
		$begintm = strtotime($params['begintm'].":00")-8*3600;
		$endtm = strtotime($params['endtm'].":59")-8*3600;		
		$date = array(			
			'uid' => Better_Registry::get('sess')->admin_uid,
			'title' => $title,
			'content' => $content,
			'begintm' => $begintm,
			'endtm' => $endtm
			);		
		$tempfile = array(
			array('imgtype' => 'banner_480','result' => 0,'url' => ''),
			array('imgtype' => 'banner_360','result' => 0,'url' => ''),
			array('imgtype' => 'banner_240','result' => 0,'url' => ''),
			array('imgtype' => 'attach_480','result' => 0,'url' => ''),
			array('imgtype' => 'attach_360','result' => 0,'url' => ''),
			array('imgtype' => 'attach_240','result' => 0,'url' => '')
		);
		
		$endfile = array();
		foreach($tempfile as $key =>$row){			
			$errorstr = $row['imgtype']; 			
			try{
				$file = &$_FILES[$row['imgtype']];				
				$fileInfo = pathinfo($file['name']);		
				$ext = strtolower($fileInfo['extension']);
				$newFileName = $act_id.".".$ext;	
				$tempinfo = split("_",$row['imgtype']);	
				$savePath = Better_Config::getAppConfig()->attachment->marketattach->save_path."/".$tempinfo[0]."/".$tempinfo[1];		
				$newFile = $savePath.'/'.$newFileName;					
				$flag = move_uploaded_File($file['tmp_name'], $newFile);						
				if(file_exists($newFile)){					
					$tempfile[$key]['result'] = 1;
					$tempfile[$key]['url'] = Better_Config::getAppConfig()->attachment->marketattach->url."/".$tempinfo[0]."/".$tempinfo[1]."/".$newFileName;
					if(!isset($endfile[$tempinfo[0]])){
						$endfile[$tempinfo[0]]['file'] = $newFile;
						$endfile[$tempinfo[0]]['code'] = $tempinfo[1];
						$endfile[$tempinfo[0]]['url'] = $tempfile[$key]['url'];
					}					
				}
			} catch(Exception $errorstr){
				Better_Log::getInstance()->logInfo($act_id."--".$errorstr."false",'activityfile');
			}
		}	
		if(count($endfile)>0){
			try{
				foreach($tempfile as $key =>$row){	
					$tempinfo = split("_",$row['imgtype']);		
					if($row['result'] == 0 && isset($endfile[$tempinfo[0]]['file'])){						
						$todofile = str_replace("/".$tempinfo[0]."/".$endfile[$tempinfo[0]]['code'],"/".$tempinfo[0]."/".$tempinfo[1],$endfile[$tempinfo[0]]['file']);				
						$handler = Better_Image_Handler::factory($endfile[$tempinfo[0]]['file']);
						$origFile = $handler->scale($endfile[$tempinfo[0]]['code'],10000);
						$thumbFile = Better_Image_Handler::factory($origFile)->scale($tempinfo[1], 1000,$tempinfo[1]."_");
						$fromfile = str_replace("/".$tempinfo[0]."/".$endfile[$tempinfo[0]]['code']."/","/".$tempinfo[0]."/".$endfile[$tempinfo[0]]['code']."/".$tempinfo[1]."_",$endfile[$tempinfo[0]]['file']);	
						copy($fromfile, $todofile);	
						unlink($fromfile);
						$tempfile[$key]['result'] = 1;
						$tempfile[$key]['url'] = str_replace("/".$tempinfo[0]."/".$endfile[$tempinfo[0]]['code'],"/".$tempinfo[0]."/".$tempinfo[1],$endfile[$tempinfo[0]]['url']);							
					}			
				}
			} catch(Exception $copyimg){
				Better_Log::getInstance()->logInfo($act_id,'imgrefreshfalse');
			}
			try{
				foreach($tempfile as $row){
					$cmdstr = "/usr/local/squid/bin/squidclient -m PURGE -p 80 -h 172.17.20.14  {URL}";
					$cmdstr = str_replace("{URL}",$row['url'],$cmdstr);
					$cmd = EscapeShellCmd($cmdstr);
					system($cmd."> /tmp/null &");
				}		
			} catch(Exception $refreshimg){
				Better_Log::getInstance()->logInfo($act_id,'imgrefreshfalse');
			}
			
			if($tempfile[0]['url']!=''){
				$date['image_url'] = $tempfile[0]['url'];
			}
			if($tempfile[5]['url']!=''){
				$date['attach_url'] = $tempfile[5]['url'];
			}
		}
		Better_DAO_Activity::getInstance()->update($date,$act_id);	
		Better_DAO_Activitypoi::getInstance()->delete($act_id,'act_id');
		$poilist = split(",",$poi_id);
		Better_Log::getInstance()->logInfo(serialize($poilist),'activitydata');	
		if(is_array($poilist)){			
			foreach($poilist as $row){
				$temppoi_id = $row;				
				if($temppoi_id>0){
					$tempdata = array(
						'poi_id' => $temppoi_id,
						'act_id' => $act_id,
						'dateline' => time()
					);
					Better_DAO_Activitypoi::getInstance()->insert($tempdata);		
				}		
			}			
		}		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/activity/donewactivity.js?ver='.BETTER_VER_CODE);
		
	}
	public function donewactivityAction(){
		$result = 1;
		$params = $this->getRequest()->getParams();		
		$poi_id = $params['poi_str'];
		$content = trim($params['content']);
		$title = trim($params['title']);		
		$begintm = strtotime($params['begintm'].":00")-8*3600;
		$endtm = strtotime($params['endtm'].":59")-8*3600;	
		$ownertype = isset($params['ownertype']) ? $params['ownertype']:0;
	
		$result = Better_Activity::create(array(			
			'creator' => Better_Registry::get('sess')->admin_uid,
			'title' => $title,
			'content' => $content,
			'begintm' => $begintm,
			'endtm' => $endtm
			));
		
		$act_id = $result['act_id'];
		$tempfile = array(
			array('imgtype' => 'banner_480','result' => 0,'url' => ''),
			array('imgtype' => 'banner_360','result' => 0,'url' => ''),
			array('imgtype' => 'banner_240','result' => 0,'url' => ''),
			array('imgtype' => 'attach_480','result' => 0,'url' => ''),
			array('imgtype' => 'attach_360','result' => 0,'url' => ''),
			array('imgtype' => 'attach_240','result' => 0,'url' => '')
		);
		$endfile = array();
		foreach($tempfile as $key =>$row){			
			$errorstr = $row['imgtype']; 			
			try{
				$file = &$_FILES[$row['imgtype']];				
				$fileInfo = pathinfo($file['name']);		
				$ext = strtolower($fileInfo['extension']);
				$newFileName = $act_id.".".$ext;	
				$tempinfo = split("_",$row['imgtype']);	
				$savePath = Better_Config::getAppConfig()->attachment->marketattach->save_path."/".$tempinfo[0]."/".$tempinfo[1];		
				$newFile = $savePath.'/'.$newFileName;		
							
				$flag = move_uploaded_File($file['tmp_name'], $newFile);						
				if(file_exists($newFile)){					
					$tempfile[$key]['result'] = 1;
					$tempfile[$key]['url'] = Better_Config::getAppConfig()->attachment->marketattach->url."/".$tempinfo[0]."/".$tempinfo[1]."/".$newFileName;
					if(!isset($endfile[$tempinfo[0]])){
						$endfile[$tempinfo[0]]['file'] = $newFile;
						$endfile[$tempinfo[0]]['code'] = $tempinfo[1];
						$endfile[$tempinfo[0]]['url'] = $tempfile[$key]['url'];
					}					
				}
			} catch(Exception $errorstr){
				Better_Log::getInstance()->logInfo($act_id."--".$errorstr."false",'activityfile');
			}
		}	
		
		if(count($endfile)>0){
			foreach($tempfile as $key =>$row){		
				$tempinfo = split("_",$row['imgtype']);	
				if($row['result'] == 0 && isset($endfile[$tempinfo[0]]['file'])){				
					$todofile = str_replace("/".$tempinfo[0]."/".$endfile[$tempinfo[0]]['code'],"/".$tempinfo[0]."/".$tempinfo[1],$endfile[$tempinfo[0]]['file']);				
					$handler = Better_Image_Handler::factory($endfile[$tempinfo[0]]['file']);
					$origFile = $handler->scale($endfile[$tempinfo[0]]['code'],10000);
					$thumbFile = Better_Image_Handler::factory($origFile)->scale($tempinfo[1], 1000,$tempinfo[1]."_");
					$fromfile = str_replace("/".$tempinfo[0]."/".$endfile[$tempinfo[0]]['code']."/","/".$tempinfo[0]."/".$endfile[$tempinfo[0]]['code']."/".$tempinfo[1]."_",$endfile[$tempinfo[0]]['file']);	
					copy($fromfile, $todofile);	
					unlink($fromfile);
					$tempfile[$key]['result'] = 1;
					$tempfile[$key]['url'] = str_replace("/".$tempinfo[0]."/".$endfile[$tempinfo[0]]['code'],"/".$tempinfo[0]."/".$tempinfo[1],$endfile[$tempinfo[0]]['url']);							
				}			
			}
			Better_Activity::updateimg(array(
				'act_id' =>$act_id,
				'image_url' =>	$tempfile[0]['url'],
				'attach_url' => $tempfile[5]['url']
			));	
		}	
		$poilist = split(",",$poi_id);
		if(is_array($poilist)){
			foreach($poilist as $row){
				$temppoi_id = $row;	
				if($temppoi_id>0){
					$tempdata = array(
						'poi_id' => $temppoi_id,
						'act_id' => $act_id,
						'dateline' => time()
					);
					Better_DAO_Activitypoi::getInstance()->insert($tempdata);
					$groupid = time();	
					if($ownertype){						
						$result = Better_Poi_Notification::create(array(
							'poi_id' => $temppoi_id,			
							'creator' => Better_Registry::get('sess')->admin_uid,
							'title' => $content,
							'content' => $content,
							'image' => $tempfile[5]['url'],
							'begintm' => $begintm,
							'endtm' => $endtm,
							'groupid' => $groupid
							));
					}
				}			
			}
		}		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/activity/donewactivity.js?ver='.BETTER_VER_CODE);
	}
	
}

?>