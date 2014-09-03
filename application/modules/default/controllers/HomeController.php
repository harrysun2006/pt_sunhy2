<?php

/**
 * 用户登录后的主页
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class HomeController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();

		$this->needLogin();
    	$this->commonMeta();

   		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/home.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
	}
	
	public function indexAction()
	{
		$this->view->inHome = true;
		
		//page 1
		define('BETTER_BLOG_LIST_CASE', 'list_follow');
		$return = array();
		$userObj = $this->uid ? $this->user : Better_User::getInstance();
		
		$return = $userObj->status()->webFollowings(array(
			'page' => 1,
			'is_following' => true,
			'type' => array('normal', 'checkin', 'tips'),
			'page_size' => BETTER_PAGE_SIZE		
			));

		$_output['rows'] = Better_Output::filterBlogs($return['rows']);
		$_output['count'] = $return['count'];
		$_output['rts'] = Better_Output::filterBlogs($return['rts']);
		$_output['pages'] = Better_Functions::calPages($return['count']);
		$_output['page'] = 1;
		$jsonPage1 = json_encode($_output);
		//$_SESSION['page_1'] = $jsonPage1;
		//end page			
		
		$this->view->headScript()->prependScript('
		betterUser.followers = '.Better_Functions::toJsArray($this->user->followers).';
		var needRef = false;
		var _page_1 = ' . $jsonPage1 . ';
		');
		
		$this->view->needCheckinJs = false;
		
		//市场活动
		if(Better_Config::getAppConfig()->webbanner->switch){
			$params = array(
				'checked' =>1,
				'page' => 1	
			);
			$result = Better_Webbanner::getAll($params);
			
			$market = $result['rows'];		
		} else {
			$market = require_once(APPLICATION_PATH.'/../public/market.php');
		}
		$this->view->market = $market;
		
		@header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		
		//未读过的提到我的数量
		$newRtCount = Better_DAO_Mentionme::getInstance($this->uid)->newMentionmeCount($this->userInfo['last_rt_mine']);
		$this->view->newRtCount = $newRtCount;	
		
		//好友数量
		$this->view->friendsCount = count($this->user->friends);
	}
	
}