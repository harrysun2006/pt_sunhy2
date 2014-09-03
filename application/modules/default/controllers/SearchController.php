<?php

/**
 * 搜索结果
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class SearchController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
		$this->needLogin();
    	$this->commonMeta();
    	
		$this->view->myfollowing = Better_User_Follow::getInstance($this->uid)->getFollowings();
		$this->view->myblocking = Better_User_Block::getInstance($this->uid)->getBlocks();
		$this->view->myfollower = Better_User_Follow::getInstance($this->uid)->getFollowers();

		$this->view->headScript()->prependScript('
		betterUser.followings = '.Better_Functions::toJsArray($this->view->myfollowing).';
		betterUser.followers = '.Better_Functions::toJsArray($this->view->myfollower).';
		betterUser.blocks = '.Better_Functions::toJsArray($this->view->myblocking).';
		');    	
    	
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/search.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
	}
	
	public function indexAction()
	{
		$this->view->visitors = $this->user->visit()->getVisitors();
		$data = $this->user->follow()->getFollowingsWithDetail(1, 18);
		$this->view->following = $data['rows'];
		
		$data = $this->user->badge()->getMyBadges();
		$this->view->badges = $data;
		
		$data = $this->user->friends()->all(1, 180);
		$this->view->friends = $data['rows'];
		
		$data = $this->user->major()->getAll(1, 10);
		$this->view->majors = $data['rows'];		
		
		$text = $this->getRequest()->getParam('search_text', '');
		$this->view->text = $text;
		$this->view->search_range = $this->getRequest()->getParam('search_range', 'poi');
		$this->view->search_page = $this->getRequest()->getParam('page', 1);
		
		$this->view->needCheckinJs = true;
	}
	
}

?>