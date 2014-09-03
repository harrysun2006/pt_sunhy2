<?php

/**
 * 用户消息Rss输出
 * 
 * @author leip <leip@peptalk.cn>
 */

class RssController extends Better_Controller_Front 
{

	public function init()
	{
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 1);
		parent::init();
	}	

	public function indexAction()
	{
		$username = $this->getRequest()->getParam('username', '');
		$user = Better_User::getInstance();
		$userInfo=$user->getUserByUsername($username);
		
		$page=1;
		$blogs = Better_User_Blog::getInstance($userInfo['uid'])->getBlogs($page, BETTER_PAGE_SIZE, array(
			'normal', 'tips', 'checkin'
			));
		
		$rss=new Better_Rss();
		$output = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>';
		$output .= $rss->generateRss($blogs, $userInfo);
		$this->getResponse()->setHeader('Content-Length', strlen($output));
		$this->getResponse()->setHeader('Content-Type', 'text/xml; charset=utf-8');
		$this->getResponse()->sendHeaders();
	}
	
}