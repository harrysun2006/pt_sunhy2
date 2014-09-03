<?php

/**
 * 用户相关KML
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Kml_UserController extends Better_Controller_Kml 
{
	
	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		$uid = (int)$this->getRequest()->getParam('uid', $this->uid);
		
		$this->output['rows'] = 0;
		$this->output['pages'] = 0;
		$this->output['count'] = 0;
		$this->output['page'] = 1;
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
	
		if ($userInfo['uid']) {
			$result = $user->blog()->getSomebody(array(
				'page' => $this->page,
				'type' => 'checkin',
				'page_size' => BETTER_PAGE_SIZE,
				'uid' => $userInfo['uid'],
				'ignore_block' => true
				));
			
			$this->output['rows'] = &$result['rows'];
			$this->output['pages'] = &$result['pages'];
			$this->output['count'] = count($result['rows']);
			$this->output['page'] = $this->page;						
		}
		
		Zend_Debug::dump($this->output);exit;
	}
}