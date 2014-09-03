<?php
/**
 * admin search POI controller
 * @author yangl
 */
class Admin_SearchpoiController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/searchpoi.js?ver='.BETTER_VER_CODE);
		//$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/city.js?ver='.BETTER_VER_CODE);
		$this->view->title="POI管理";		
	}
	
	public function indexAction()
	{ 
		$params = $this->getRequest()->getParams();
		$result = array();
		if($params['namekeyword'] || $params['placekeyword']){
			$result=Better_Admin_Poi::getPOIs($params);
		}
		//Zend_Debug::dump($params);exit();
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	
	public function mergemutiAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		$target_pid = $params['target_pid'] ? $params['target_pid'] : '';
		$pids = $params['pids']? (array)$params['pids']: array();
		
		if($pids && $target_pid){
			
			$result = Better_Admin_Simipoi::mergeMutiPOI($pids, $target_pid);
			
		}
	
		$this->sendAjaxResult($result);
	}
	

}

?>