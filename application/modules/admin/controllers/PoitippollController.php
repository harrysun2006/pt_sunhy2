<?php
/**
 * admin Poi tip poll controller
 * @author yangl
 */
class Admin_PoitippollController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/poitippoll.js?ver='.BETTER_VER_CODE);
		$this->view->title="POI点评投票管理";		
	}
	
	public function indexAction()
	{ 
		$params = $this->getRequest()->getParams();
		
		$result=Better_Admin_Poitippoll::getPoitippolls($params);
		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	
}

?>