<?php

/**
 * 找朋友首页
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class PeopleController extends Better_Controller_Front 
{
	
	public function init()
	{
		parent::init();
		$this->needLogin();
		$this->commonMeta();
 
		$this->view->myfollowing = Better_User_Follow::getInstance($this->uid)->getFollowings();
		$this->view->myblocking = Better_User_Block::getInstance($this->uid)->getBlocks();
 
		//$this->appendScript($this->jsUrl.'/functions/peoples.js?ver='.BETTER_VER_CODE);
    	//$this->appendScript($this->jsUrl.'/controllers/people.js?ver='.BETTER_VER_CODE);
    	
	}
	
	public function indexAction()
	{
		$this->_helper->getHelper('Redirector')->gotoSimple('invitation','');
		exit(0);
	}

	public function findAction()
	{
		$this->_helper->getHelper('Redirector')->gotoUrl('invitation/find');
		exit(0);
		
		$this->view->email_domains = array(
			'sina.com', 'sohu.com', 'tom.com', '126.com', '163.com', 'yeah.net', 'gmail.com',
		// 'yahoo.com', 'yahoo.com.cn', 'yahoo.cn',
			);
			
		$userInfo = Better_Registry::get('user')->getUser();
		
		$data = Better_User_Follow::getInstance($userInfo['uid'])->getFollowingsWithDetail(1, 18);
		$this->view->following = $data['rows'];
				
		$lon = $userInfo['lon'];
		$lat = $userInfo['lat'];
		
		$results = Better_Search::factory(array(
			'what' => 'user',
			'lon' => $lon,
			'lat' => $lat,
			'range' => 5000,
			'page' => $this->page
			))->search();

		$this->view->aroundUsers = $results['rows'];
	}

	
}

?>