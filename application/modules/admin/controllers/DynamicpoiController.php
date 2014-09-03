<?php
/**
 * admin POI controller
 * @author yangl
 */
class Admin_DynamicpoiController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/dypoi.js?ver='.BETTER_VER_CODE);
		$this->view->title="活跃POI";		
	}
	
	public function indexAction()
	{ 
		exit(0);
		$params = $this->getRequest()->getParams();
		$params['dyna'] = 1;
		$result=Better_Admin_Poi::getPOIs($params);
		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
		
		
	}
	
	
}

?>