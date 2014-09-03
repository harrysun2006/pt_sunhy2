<?php
/**
 * admin user check in controller
 * @author yangl
 */
class Admin_UsercheckinController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/usercheckin.js?ver='.BETTER_VER_CODE);
		$this->view->title="用户入驻历史";		
	}
	
	public function indexAction()
	{ 
		$params = $this->getRequest()->getParams();
		
		$result=Better_Admin_Usercheckin::getUserCheckins($params);
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	
}

?>