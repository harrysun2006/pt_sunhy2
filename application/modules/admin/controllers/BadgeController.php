<?php
/**
 * admin POI controller
 * @author yangl
 */
class Admin_BadgeController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/badge.js?ver='.BETTER_VER_CODE);
		$this->view->title="勋章管理";		
		$had_specialsync = Better_Config::getAppConfig()->badge->had_specialsync;
		$blog_type = Better_Config::getAppConfig()->badge->blog_type;	
		$user_gender = Better_Config::getAppConfig()->badge->user_gender;		
		$this->view->headScript()->prependScript('
		var badge_blog_type =\''.$blog_type.'\';		
		');	
		$this->view->headScript()->prependScript('
		var badge_had_specialsync =\''.$had_specialsync.'\';		
		');
		$this->view->headScript()->prependScript('
		var badge_user_gender =\''.$user_gender.'\';		
		');	
	}
	
	public function indexAction()
	{ 
		$params = $this->getRequest()->getParams();
		
		$result=Better_Admin_Badge::getBadges($params);		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	
	
	public function updateAction(){
		$result=0;
		$params = $this->getRequest()->getParams();
		$xid=isset($params['xid'])? $params['xid']:'';
		$name=isset($params['name'])? $params['name']:'';
		$pic=isset($params['pic'])? $params['pic']:'';
		
		if($name && $pic){
			$data=array(
				'badge_name'=>$name,
				'badge_picture'=>$pic
			);
		}else{
			$data=array();
		}
		
		if($xid && count($data)>0){
			Better_Admin_Badge::updateBadge($data, $xid) && $result=1;
		}		
		$this->sendAjaxResult($result);		
	}
	
	public function newbadgeAction(){
		
		$this->view->headScript()->appendFile($this->jsUrl.'/jquery/mixed.js?ver='.BETTER_VER_CODE);		
			
	}
	public function donewbadgeAction(){
		$result = 1;
		$params = $this->getRequest()->getParams();		
		$badge_name = trim($params['badge_name']);
		$got_tips = trim($params['got_tips']);
		$sync_tips = trim($params['sync_tips']);		
		$btm = strlen($params['btm'])>0 ?strtotime($params['btm'].":00")-8*3600 :0;
		$etm = strlen($params['etm'])>0 ?strtotime($params['etm'].":59")-8*3600 :0;				
		$family = trim($params['family']);
		$category = trim($params['category']);
		$gender = trim($params['gender']);		
		$info = trim($params['info']);
		$condition = trim($params['condition']);		    
		$result = Better_Badge::create(array(		
			'badge_name' => $badge_name,				
			'got_tips' => $got_tips,
			'sync_tips' => $sync_tips,				
			'btm' => $btm,
			'etm' => $etm,
			'family' => $family,
			'category' => $category,
			'gender' => $gender,
			'info' => $info,
			'condition' => $condition,
			'category' => $category
			));
		
		$b_id = $result['id'];
		Better_Log::getInstance()->logInfo("\n".$b_id,'badgefile');
		$tempfile = array(
			array('imgtype' => 'badge_big','result' => 0,'url' => ''),
			array('imgtype' => 'badge_96','result' => 0,'url' => ''),
			array('imgtype' => 'badge_100','result' => 0,'url' => ''),
			array('imgtype' => 'badge_48w','result' => 0,'url' => ''),
			array('imgtype' => 'badge_36','result' => 0,'url' => ''),
			array('imgtype' => 'badge_24','result' => 0,'url' => '')
		);
		$endfile = array();
		foreach($tempfile as $key =>$row){			
			$errorstr = $row['imgtype']; 			
			try{
				$file = &$_FILES[$row['imgtype']];				
				$fileInfo = pathinfo($file['name']);		
				$ext = strtolower($fileInfo['extension']);
				$newFileName = $b_id.".".$ext;	
				$tempinfo = split("_",$row['imgtype']);	
				$tempfolder = "/".$tempinfo[1];
				if($tempinfo[1]==100){
					$tempfolder = "";
				}
				$savePath = Better_Config::getAppConfig()->attachment->badgeimg->save_path.$tempfolder;				
				$newFile = $savePath.'/'.$newFileName;	
				Better_Log::getInstance()->logInfo("\n".$newFile,'badgefile');						
				$flag = move_uploaded_File($file['tmp_name'], $newFile);				
			} catch(Exception $errorstr){
				Better_Log::getInstance()->logInfo($id."--".$errorstr."false",'badgefileerror');
			}
		}
		try{
			$cmdstr = Better_Config::getAppConfig()->badge->clearcache;			
			$cmd = EscapeShellCmd($cmdstr);
			system($cmd."> /tmp/null &");
		} catch(Exception $ccache){
			Better_Log::getInstance()->logInfo('','badgecache');
		}		
		try {
			Better_Cache::remote()->remove('kai_badges');
		} catch(Exception $bbccache){
				Better_Log::getInstance()->logInfo('','badgecache');
		}			
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/badge/donewbadge.js?ver='.BETTER_VER_CODE);
	}
	
	public function updatebadgeAction(){
		$this->view->headScript()->appendFile($this->jsUrl.'/jquery/mixed.js?ver='.BETTER_VER_CODE);
		$id = $this->getRequest()->getParam('id', '');

		$params = array(
			'id'  => $id		
		);
		if ($id>0) {		
			$activity = Better_DAO_Badge::getInstance()->get($params);			
		}		
		
		$this->view->badge = $activity;		
	}
	
	public function doupdatebadgeAction(){
		$result = 1;
		$params = $this->getRequest()->getParams();		
		$params = $this->getRequest()->getParams();		
		$badge_name = trim($params['badge_name']);
		$got_tips = trim($params['got_tips']);
		$sync_tips = trim($params['sync_tips']);		
		$btm = strlen($params['btm'])>0 ?strtotime($params['btm'].":00")-8*3600 :0;
		$etm = strlen($params['etm'])>0 ?strtotime($params['etm'].":59")-8*3600 :0;			
		$family = trim($params['family']);
		$category = trim($params['category']);
		$gender = trim($params['gender']);		
		$info = trim($params['info']);
		$condition = trim($params['condition']);
		$id = trim($params['id']);
		$b_id = $id;
		$tempfile = array(
			array('imgtype' => 'badge_big','result' => 0,'url' => ''),
			array('imgtype' => 'badge_96','result' => 0,'url' => ''),
			array('imgtype' => 'badge_100','result' => 0,'url' => ''),
			array('imgtype' => 'badge_48w','result' => 0,'url' => ''),
			array('imgtype' => 'badge_36','result' => 0,'url' => ''),
			array('imgtype' => 'badge_24','result' => 0,'url' => '')
		);
		$endfile = array();
		foreach($tempfile as $key =>$row){			
			$errorstr = $row['imgtype']; 			
			try{
				$file = &$_FILES[$row['imgtype']];	
								
				$fileInfo = pathinfo($file['name']);	
						
				$ext = strtolower($fileInfo['extension']);
				if(!$ext){
					continue;
				}
				$newFileName = $b_id.".".$ext;	
				$tempinfo = split("_",$row['imgtype']);	
				$tempfolder = "/".$tempinfo[1];
				if($tempinfo[1]==100){
					$tempfolder = "";
				}
				$savePath = Better_Config::getAppConfig()->attachment->badgeimg->save_path.$tempfolder;				
				$newFile = $savePath.'/'.$newFileName;					
				if(file_exists($newFile)){
					unlink($newFile);
				}				
				$flag = move_uploaded_File($file['tmp_name'], $newFile);											
				$endfile[] = Better_Config::getAppConfig()->attachment->badgeimg->url.$tempfolder."/".$newFileName;								
			} catch(Exception $errorstr){
				Better_Log::getInstance()->logInfo($id."--".$errorstr."false",'badgefileerror');
			}
		}	
		if(count($endfile)>0){			
			try{
				foreach($endfile as $row){
					$cmdstr = "/usr/local/squid/bin/squidclient -m PURGE -p 80 -h 172.17.20.14  {URL}";
					$cmdstr = str_replace("{URL}",$row,$cmdstr);
					$cmd = EscapeShellCmd($cmdstr);
					system($cmd."> /tmp/null &");
				}		
			} catch(Exception $refreshimg){
				Better_Log::getInstance()->logInfo($act_id,'imgrefreshfalse');
			}			
		}
		$date = array(				
			'badge_name' => $badge_name,				
			'got_tips' => $got_tips,
			'sync_tips' => $sync_tips,				
			'btm' => $btm,
			'etm' => $etm,
			'family' => $family,
			'category' => $category,
			'gender' => $gender,
			'info' => $info,
			'condition' => $condition,
			'category' => $category
			);
		Better_DAO_Badge::getInstance()->update($date,$id);			
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/badge/donewbadge.js?ver='.BETTER_VER_CODE);
		try{
			$cmdstr = Better_Config::getAppConfig()->badge->clearcache;			
			$cmd = EscapeShellCmd($cmdstr);			
			system($cmd."> /tmp/null &");
		} catch(Exception $ccache){
				Better_Log::getInstance()->logInfo('','badgecache');
		}	
		try {
			Better_Cache::remote()->remove('kai_badges');
		} catch(Exception $bbccache){
				Better_Log::getInstance()->logInfo('','badgecache');
		}	
	}
}

?>