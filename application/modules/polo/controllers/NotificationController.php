<?php

/**
 * NotificationController
 * 
 * @author
 * @version 
 */

//require_once 'Zend/Controller/Action.php';

class Polo_NotificationController extends Better_Mobile_Front  {
	
	public static $REQUEST_MSG = 'rm';		//请求
	public static $SYSTEM_MSG ='sm';		//系统
	public static $DIRECT_MSG = 'dm';		//站内信
	public static $GAME_MSG = "gm";
	
	public static $REQUEST_FRIEND = 'rfr';
	public static $REQUEST_FOLLOW = 'rfl';
	public static $REQUEST_BLOCK = 'rbl';

	private static $OPTION_DESTROY = 'dt';
	private static $OPTION_AGREE = "ag";
	private static $OPTION_REJECT = 'rj';
	
	private static $maxActivities = 6;
	
	public function init()
	{
		parent::init();
		$this->poloneedLogin();
	}
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
		$page = (int)$_GET['page'];
		if( $page < 1 )	
			$page = 1;
		$notifications = Better_User_Notification_All::getInstance($this->uid)->getReceiveds(array(
		'page' => $page,
		'count' => self::$maxActivities,
		'type' => array(
			'direct_message',
			'follow_request',
			'friend_request'
			),
		));

	//	zend_debug::dump($notifications);
		$this->view->start = ($page - 1) * self::$maxActivities + 1;
		$this->view->notifications = $notifications['rows'];
		$this->view->senders = $notifications['users'];
		$this->view->page = $page;
		//Zend_Debug::dump($notifications);		
		if( count($this->view->notifications) >= self::$maxActivities )
			$this->view->urlNext = "<a href=\"/polo/notification/index?page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
		if( $page > 1 )
			$this->view->urlPrev = "<a href=/polo/notification/index?page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
	}
	
	public function deleteAction(){
		$msgId = $_GET['mid'];
		$page = $_GET['page'];
		if( $msgId ){
			$deleted = Better_User_Notification::getInstance($this->uid)->delReceived((int)($msgId));
		}
		$this->_redirect('/polo/notification/?page='.$page);
	}
	
	public function sendAction(){
		$recId = $_GET['uid'];
		if( $recId ){
			$receiver = Better_User::getInstance($recId)->getUserInfo();
			$this->view->receiver = $receiver;
		}else{
			trigger_error("The reciever must be specified.");
		}
	}
	
	public function sendoutAction(){
		$params = $this->getRequest()->getPost();
		$result = $this->user->notification()->directMessage()->send(array(
					'content' => $params['content'],
					'receiver' => $params['receiver']
					));
					
		$this->_redirect('/polo/user?uid='.$params['receiver']);
	}
	
	/**
	 * Delete all notifications
	 *
	 */
	public function clearAction(){
		$this->user->notification()->directMessage()->clear(0);
		$this->_redirect('/polo/notification');
	}
	
	public function requestAction(){
		
		$category = $_GET['cat'];
		$subcategory = $_GET['subcat'];
		$option = $_GET['option'];
		$target = (int)($_GET['target']);
		
		switch( $category ){
			case self::$REQUEST_MSG:
				if( $subcategory == self::$REQUEST_FRIEND ){
					$this->friendrequest( $target, $option );
				}
				else if ($subcategory == self::$REQUEST_FOLLOW ){
					$this->followrequest( $target, $option );
				}
				break;
			case self::$SYSTEM_MSG:
				break;
			case self::$DIRECT_MSG:
				break;
			case self::$GAME_MSG:
				break;
			default:
				//Zend_Debug::dump($category);
				exit(0);
				break;
		}
		
		$this->_redirect('/polo/notification');
	}
	
	private function friendrequest( $target, $option ){
		
		if( $target > 0 ){
			if( 0 != strcmp($option, self::$OPTION_AGREE) ){
				Better_User_Friends::getInstance($this->uid)->reject($target);
			}else{				
				Better_User_Friends::getInstance($this->uid)->agree($target);
			}
		}
	}
	
	private function followrequest( $target, $option ){
		
		if( $target > 0 ){
			if( 0 != strcmp($option, self::$OPTION_AGREE) ){
				Better_User_Follow::getInstance($this->uid)->reject($target);
			}else{				
				Better_User_Follow::getInstance($this->uid)->agree($target);
			}
		}
	}
	

}

