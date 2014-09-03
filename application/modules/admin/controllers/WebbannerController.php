<?php
/**
 * admin POI controller
 * @author yangl
 */
class Admin_WebbannerController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();						
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/webbanner.js?ver='.BETTER_VER_CODE);
		$this->view->title="Banner管理";		
	}	
	
	
	public function indexAction(){		
		$params = $this->getRequest()->getParams();		
		$result=Better_Webbanner::getAll($params);			
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	
	}
	
	public function updatebannerAction(){
		$this->view->headScript()->appendFile($this->jsUrl.'/jquery/mixed.js?ver='.BETTER_VER_CODE);
		$nid = $this->getRequest()->getParam('id', '');
	
		$specialDetail = array();		
		$params = array(
			'id'  => $nid		
		);
		if ($nid>0) {
			$banner = Better_DAO_Webbanner::getInstance()->get($params);
		}		
		$this->view->banner = $banner;		
	}
	public function newbannerAction(){
		$this->view->headScript()->appendFile($this->jsUrl.'/jquery/mixed.js?ver='.BETTER_VER_CODE);		
		
	}
	public function doupdatebannerAction(){
		$result = 1;
		$params = $this->getRequest()->getParams();		
		$act_id = (int)$params['id'];
		$rank = (int)$params['rank'];	
		$link = trim($params['link']);		
		$begintm = strtotime($params['begintm'].":00")-8*3600;
		$endtm = strtotime($params['endtm'].":59")-8*3600;			
		$date = array(			
			'link' => $link,
			'rank' => $rank,
			'begintm' => $begintm,
			'endtm' => $endtm,
			'uptm' => time()
			);		
		$tempfile = array(
			array('imgtype' => 'webbanner','result' => 0,'url' => ''),		
		);
		$endfile = array();
		foreach($tempfile as $key =>$row){			
			$errorstr = $row['imgtype']; 			
			try{
				$file = &$_FILES[$row['imgtype']];				
				$fileInfo = pathinfo($file['name']);		
				$ext = strtolower($fileInfo['extension']);
				$newFileName = $act_id.".".$ext;				
				$savePath = Better_Config::getAppConfig()->attachment->marketattach->save_path."/".$row['imgtype'];		
				$newFile = $savePath.'/'.$newFileName;							
				$flag = move_uploaded_File($file['tmp_name'], $newFile);						
				if(file_exists($newFile)){					
					$tempfile[$key]['result'] = 1;
					$tempfile[$key]['url'] = Better_Config::getAppConfig()->attachment->marketattach->url."/".$row['imgtype']."/".$newFileName;
								
				}
			} catch(Exception $errorstr){
				Better_Log::getInstance()->logInfo($act_id."--".$errorstr."false",'webbannerfile');
			}
		}
		if($tempfile[0]['url']!=''){
			try{
				$cmdstr = "/usr/local/squid/bin/squidclient -m PURGE -p 80 -h 172.17.20.14  {URL}";
				$cmdstr = str_replace("{URL}",$tempfile[0]['url'],$cmdstr);
				$cmd = EscapeShellCmd($cmdstr);
				system($cmd."> /tmp/null &");
			} catch(Exception $upimgcache){
				
			}
			$date['imageurl'] = $tempfile[0]['url'];
		}
		Better_DAO_Webbanner::getInstance()->update($date,$act_id);			
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/webbanner/do.js?ver='.BETTER_VER_CODE);
		
	}
	public function donewbannerAction(){
		$result = 1;
		$params = $this->getRequest()->getParams();	
		$link = trim($params['link']);		
		$begintm = strtotime($params['begintm'].":00")-8*3600;
		$endtm = strtotime($params['endtm'].":59")-8*3600;	
		$rank = $params['rank']>0 ? $params['rank']:1;
		
		$result = Better_Webbanner::create(array(		
			'link' => $link,
			'content' => $content,
			'begintm' => $begintm,
			'endtm' => $endtm,
			'rank' => $rank
			));		
		$act_id = $result['id'];
		$tempfile = array(
			array('imgtype' => 'webbanner','result' => 0,'url' => ''),		
		);
		$endfile = array();
		foreach($tempfile as $key =>$row){			
			$errorstr = $row['imgtype']; 			
			try{
				$file = &$_FILES[$row['imgtype']];				
				$fileInfo = pathinfo($file['name']);		
				$ext = strtolower($fileInfo['extension']);
				$newFileName = $act_id.".".$ext;				
				$savePath = Better_Config::getAppConfig()->attachment->marketattach->save_path."/".$row['imgtype'];		
				$newFile = $savePath.'/'.$newFileName;							
				$flag = move_uploaded_File($file['tmp_name'], $newFile);						
				if(file_exists($newFile)){					
					$tempfile[$key]['result'] = 1;
					$tempfile[$key]['url'] = Better_Config::getAppConfig()->attachment->marketattach->url."/".$row['imgtype']."/".$newFileName;
								
				}
			} catch(Exception $errorstr){
				Better_Log::getInstance()->logInfo($act_id."--".$errorstr."false",'webbannerfile');
			}
		}	
		if($tempfile[0]['url']!=''){
			
			Better_Webbanner::updateimg(array(
				'id' =>$act_id,
				'imageurl' =>$tempfile[0]['url']
			));		
		}	
	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/webbanner/do.js?ver='.BETTER_VER_CODE);
	}
	
}

?>