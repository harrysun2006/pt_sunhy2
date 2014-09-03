<?php

/**
 * 评论详情页
 *
 * @package Controllers
 * @author yangl
 */

class CommentsController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
		$this->needLogin();
    	$this->commonMeta();
    	
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/comments.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
	}
	
	public function indexAction()
	{
		$bid = $this->getRequest()->getParam('bid', '');
		list($blog_uid, $i) = explode('.', $bid);
		$this->view->isyou = false;
		if($blog_uid==$this->uid){
			$this->view->isyou = true;
		} 
		if($blog_uid){
			$this->dispUser = Better_User::getInstance($blog_uid);
    		$this->dispUserInfo = $this->dispUser->getUser();
    		
    		$this->view->dispUser = $this->dispUser;
    		$this->view->dispUserInfo = $this->dispUserInfo;
    		
    		$this->view->visitors = $this->dispUser->visit()->getVisitors();
			$data = $this->dispUser->follow()->getFollowingsWithDetail(1, 18);
			$this->view->following = $data['rows'];
			
			$data = $this->dispUser->badge()->getMyBadges();
			$this->view->badges = $data;
			
			$data = $this->dispUser->friends()->all(1, 180);
			$this->view->friends = $data['rows'];
			
			$data = $this->dispUser->major()->getAll(1, 10);
			$this->view->majors = $data['rows'];		
		}else{
			throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
		}
		
		if($bid){
			$blog = Better_User_Status::getInstance($this->uid)->byBid($bid);
			
			//Zend_Debug::dump($blog);exit();
			if($blog['bid']){
			
				$this->view->headScript()->appendScript('
					var blog = {};
					blog.msg = "'.$blog['message'].'";
					blog.nickname = "'.$blog['nickname'].'";
					blog.username = "'.$blog['username'].'";
					blog.address = "'.$blog['poi']['name'].'";
					blog.from = "'.$blog['type'].'";
					blog.bid = "'.$blog['bid'].'";
					blog.allow_rt = parseInt('.$blog['allow_rt'].');
					blog.with_upbid = false;
					blog.now_message = "'.$blog['message'].'";
					blog.now_nickname = "'.$blog['nickname'].'";
					blog.now_bid = "'.$blog['bid'].'";
					blog.commentpage=true;
				');
				
				if($blog['upbid']){
					$upblog = Better_User_Status::getInstance($this->uid)->byBid($blog['upbid']);
					if($upblog['bid']){
						
						$this->view->headScript()->appendScript('
							blog.msg = "'.$upblog['message'].'";
							blog.nickname = "'.$upblog['nickname'].'";
							blog.username = "'.$upblog['username'].'";
							blog.address = "'.$upblog['poi']['name'].'";
							blog.poiid = "'.$upblog['poi']['poi_id'].'";
							blog.from = "'.$upblog['type'].'";
							blog.bid = "'.$upblog['bid'].'";
							blog.allow_rt = parseInt('.$upblog['allow_rt'].');
							blog.with_upbid = true;
							blog.commentpage=true;
						');
						
						$this->view->upblog = $upblog;
					}
				}
				$this->view->blog = $blog;
			}else{
				throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
			}
			
			//取收藏
			$this->view->favorites = $this->user->favorites;
		}
		
		
	}
	
}

?>
