<?php
/**
 * admin user badge controller
 * @author yangl
 */
class Admin_UserbadgeController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/userbadge.js?ver='.BETTER_VER_CODE);
		$this->view->title="用户勋章";		
	}
	
	public function indexAction()
	{ 
		$params = $this->getRequest()->getParams();
		
		$result=Better_Admin_Userbadge::getUserBadges($params);
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	
}

?>