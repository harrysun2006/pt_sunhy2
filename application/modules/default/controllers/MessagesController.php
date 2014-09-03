<?php

/**
 *  站内私信控制器
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class MessagesController extends Better_Controller_Front
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
		betterUser.followings = '.json_encode($this->view->myfollowing).';
		betterUser.followers = '.json_encode($this->view->myfollower).';
		betterUser.blocks = '.json_encode($this->view->myblocking).';
		');

    	$this->appendScript($this->jsUrl.'/controllers/messages.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
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
		
		$this->view->friendRequestCount = $this->user->notification()->friendRequest()->count(array(
			'type' => 'friend_request',
			));
		$this->view->followRequestCount = $this->user->notification()->followRequest()->count(array(
			'type' => 'follow_request',
			));
	}
		
}