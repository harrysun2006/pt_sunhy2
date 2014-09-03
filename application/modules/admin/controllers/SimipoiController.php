<?php
/**
 * admin POI controller
 * @author yangl
 */
class Admin_SimipoiController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/simipoi.js?ver='.BETTER_VER_CODE);
		$this->view->title="相似POI管理";		
	}
	
	public function indexAction()
	{ 
		$params = $this->getRequest()->getParams();
		
		$result=Better_Admin_Simipoi::getSimiPOIs($params);
		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	public function delAction(){
		$result=0;
		$params = $this->getRequest()->getParams();
		$refid = isset($params['refid']) ? $params['refid'] : 0;
		Better_Admin_Simipoi::deleteSimiByRefid($refid, $params['t']) && $result=1;
		Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addPoiLog($refid, 'no_merge_poi', '不合并POI:<br>refid=>'.$refid);
		$this->sendAjaxResult($result);
	}
	
	
	public function mergeAction(){
		$return = 0;
		
		$params = $this->getRequest()->getParams();
		$pids = $params['pids'] ? $params['pids']: array();
		$target_pid = $params['target_pid']? $params['target_pid']: 0;
		$old_refid = $params['old_refid']? $params['old_refid']: 0;
		
		if($pids && $target_pid &&$old_refid){
			$refParams = array(
				'pids'=> $pids,
				'target_poi_id' => $target_pid
			);	
			
			Better_Admin_Poi::refMutiPOI($refParams);
			$return = Better_Admin_Simipoi::mergeMutiPOI($pids, $target_pid);
			
			if($return==1){
				Better_Admin_Simipoi::deleteSimiByRefid($old_refid, $params['t']);
			}
			
		}/*else if(!$pids){
			$return = Better_Admin_Simipoi::deleteSimiByRefid($target_pid);
		}*/
		
		$this->sendAjaxResult($return);
	}
	
	
	public function mergemutiAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		$rows = $params['rows']? json_decode($params['rows'], 1) : '';
		$nids = $params['nids'] ? $params['nids'] : array();
		
		if($rows){
			foreach($rows as $target_pid=>$val){
				$old_refid = $val['old_refid'];
				$pids = $val['rs'];
				if($pids && $target_pid && $old_refid){
					$refParams = array(
						'pids'=> $pids,
						'target_poi_id' => $target_pid
					);	
					
					Better_Admin_Poi::refMutiPOI($refParams);
					$return = Better_Admin_Simipoi::mergeMutiPOI($pids, $target_pid);
					
					if($return==1){
						Better_Admin_Simipoi::deleteSimiByRefid($old_refid, $params['t']);
					}
				}
			}
		}
		
		if($nids){
			foreach($nids as $nid){
				Better_Admin_Simipoi::deleteSimiByRefid($nid, $params['t']);
				Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addPoiLog($nid, 'no_merge_poi', '不合并POI:<br>refid=>'.$nid);
			}
		}
		
		$result = 1;
		$this->sendAjaxResult($result);
	}
	
	
	public function delmutiAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		$refids = isset($params['ids']) ? $params['ids'] : 0;
		foreach($refids as $refid){
			Better_Admin_Simipoi::deleteSimiByRefid($refid, $params['t']);
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addPoiLog($refid, 'no_merge_poi', '不合并POI:<br>refid=>'.$refid);
		}
		$result = 1;
		$this->sendAjaxResult($result);
		
	}
	
	
	public function mergemanuAction(){
		$return = 0;
		$params = $this->getRequest()->getParams();
		$pids = $params['pids'] ? $params['pids']: array();
		$target_pid =  $params['target_pid'] ? $params['target_pid']: '';
		
		if($pids && $target_pid){
			$refParams = array(
				'pids'=> $pids,
				'target_poi_id' => $target_pid
			);	
			
			Better_Admin_Poi::refMutiPOI($refParams);
			$return = Better_Admin_Simipoi::mergeMutiPOI($pids, $target_pid);
		}
		
		$this->sendAjaxResult($return);
	}
	
}

?>