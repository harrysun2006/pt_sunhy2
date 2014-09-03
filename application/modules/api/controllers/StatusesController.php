<?php

/**
 * 发微博相关
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_StatusesController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		
		$this->xmlRoot = 'statuses';
	}	
	
	/**
	 * 6.8举报消息
	 * 
	 * @return
	 */
	public function reportAction()
	{
		$this->xmlRoot = 'message';
		$this->auth();
		//$this->needSufficientKarma();
		$this->needPost();
		
		$id = trim($this->getRequest()->getParam('id', ''));
		$reason = trim($this->getRequest()->getParam('reason', 'others'));
		
		$data = Better_Blog::getBlog($id);
		if (isset($data['blog']['bid'])) {
			$denounce = Better_Denounce::factory('blog');
			
			if (!$denounce->denounced($this->uid, $id, $reason)) {
				$flag = $denounce->denounce(array(
					'bid' => $id,
					'uid' => $this->uid,
					'reason' => $reason,
					'content' => $data['blog']['message'],
					));
				if ($flag) {
					$this->data[$this->xmlRoot] = $this->lang->denounce->blog->success;
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->serverError();
				}				
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.duplicate_report');
			}

		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statues.invalid_id');
		}
		
		$this->output();
	}

	/**
	 * 6.1 最新动态（随便看看）
	 * 
	 * @return
	 */
	public function publictimelineAction()
	{
		$this->auth();
		
		list($lon, $lat) = $this->mixLL();
		$range = (int)$this->getRequest()->getParam('range', 5000);
		$params = array(
			'page' => $this->page,
			);
		
		if ($lon && $lat) {
			$params['lon'] = $lon;
			$params['lat'] = $lat;
			$params['range'] = $range;
		}
		
		$rows = $this->user->blog()->getAllBlogs($params, $this->count, (int)$this->config->public_karma_limit);
		
		foreach ($rows['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'status' => $this->api->getTranslator('status')->translate(array(
					'data' => &$row,
					'userInfo' => $userInfo,
					)),
				);
		}		
		
		$this->output();
	}
	
	/**
	 * 好友的
	 * 
	 * @return
	 */
	public function friendstimelineAction()
	{
		$userInfo = $this->auth();
		$username = $this->getRequest()->getParam('id', '');
		
		if ($username=='') {
			$user = Better_Registry::get('user');
			$username = $userInfo['username'];
			$uid = $userInfo['uid'];
		} else {
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByUsername($username);
			
			if (!$userInfo['uid']) {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.user_not_found');
			} else {
				$uid = $userInfo['uid'];
			}
		}
		
		$followings = Better_User::getInstance($userInfo['uid'])->follow()->getFollowings();
		$followings[] = $uid;
		$rows = $this->user->blog()->getFollowingsBlogs($this->page, $this->count);
		$i = 0;
		foreach ($rows['rows'] as $row) {
			$this->data[$this->xmlRoot][$i++] = array(
				'status' => $this->api->getTranslator('status')->translate(array(
					'data' => &$row,
					'userInfo' => &$userInfo
					)),
				);
		}		
		
		$this->output();
	}
	
	/**
	 * 某个用户的
	 * 
	 * @return
	 */
	public function usertimelineAction()
	{
		$userInfo = $this->auth();
		$username = $this->getRequest()->getParam('id', '');
		$page = $this->getRequest()->getParam('page', 1);
		
		if ($username=='') {
			$user = Better_Registry::get('user');
			$username = $userInfo['username'];
			$uid = $userInfo['uid'];
		} else {
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByUsername($username);
			
			if (!$userInfo['uid']) {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.user_not_found');
			} else {
				$uid = $userInfo['uid'];
			}
		}
		
		$rows = Better_Blog::getByUids(array($uid), $page);
		$i = 0;
		foreach ($rows['rows'] as $row) {
			$this->data[$this->xmlRoot][$i++] = array(
				'status' => $this->api->getTranslator('status')->translate(array(
					'data' => &$row,
					'userInfo' => &$userInfo
					)),
				);
		}		
		
		$this->output();
	}
	
	/**
	 * 回复我的
	 * 
	 * @return
	 */
	public function mentionsAction()
	{
		$userInfo = $this->auth();
		$username = $this->getRequest()->getParam('id', '');
		$page = $this->getRequest()->getParam('page', 1);
		
		if ($username=='') {
			$user = Better_Registry::get('user');
			$username = $userInfo['username'];
			$uid = $userInfo['uid'];
		} else {
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByUsername($username);
			
			if (!$userInfo['uid']) {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.user_not_found');
			} else {
				$uid = $userInfo['uid'];
			}
		}
		
		$rows = Better_Blog::getRepliesByUids(array($uid), $page);
		$i = 0;
		foreach ($rows['rows'] as $row) {
			$this->data[$this->xmlRoot][$i++] = array(
				'status' => $this->api->getTranslator('status')->translate(array(
					'data' => &$row,
					'userInfo' => &$userInfo
					)),
				);
		}		
		
		$this->output();
	}
	
	/**
	 * 某个地方的
	 * 
	 * @return
	 */
	public function placetimelineAction()
	{
		$userInfo = $this->auth();
		
		list($lon, $lat) = $this->mixLL();
		$range = $this->getRequest()->getParam('range', 5000);
		
		$count = $this->getRequest()->getParam('count', 20);
		$count = $count>50 ? 50 : $count;
		$count = $count<=0 ? 20 : $count;

		if (!$lat || !$lon) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.coordinate_invalid');
		}

		$result = Better_Search::factory(array(
			'what' => 'blog',
			'method' => 'mysql',
			'lon' => $lon,
			'lat' => $lat,
			'range' => $range,
			'page' => $this->page,
			'count' => $count
			))->search();

		$i = 0;
		foreach ($result['rows'] as $row) {
			$this->data[$this->xmlRoot][$i++] = array(
				'status' => $this->api->getTranslator('status')->translate(array(
					'data' => &$row,
					'userInfo' => &$userInfo
					)),
				);
		}			
		
		$this->output();
	}
	
	/**
	 * 6.3 搜索消息
	 * 
	 * @return
	 */
	public function searchAction()
	{
		$userInfo = $this->auth();
		
		$query = trim($this->getRequest()->getParam('query', ''));
		$category = $this->getRequest()->getParam('category', 'all');
		
		if ($query=='' && $category!='checkin') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.query_required');
		}

		$rows = Better_Search::factory(array(
			'what' => 'blog',
			'keyword' => $query,
			'type' => $category,
			'page' => $this->page,
			'count' => $this->count,
			))->search();

		foreach ($rows['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'status' => $this->api->getTranslator('status')->translate(array(
					'data' => &$row,
					'userInfo' => &$userInfo
					)),
				);
		}
		
		$this->output();
	}

	/**
	 * 6.5 删除消息
	 * 
	 * @return
	 */
	public function destroyAction()
	{
		$this->xmlRoot = 'status';
		$bid = $this->id;
		
		$this->needPost();
		$userInfo = $this->auth();
		
		if ($bid) {
			$blog = Better_Blog::getBlog($bid);
			$data = array();

			if (isset($blog['user']) && isset($blog['blog']['uid']) && $blog['blog']['uid']==$userInfo['uid']) {
				
				if (Better_Blog::delete($blog['blog']['bid'])) {
					$this->data[$this->xmlRoot] = $this->api->getTranslator('status')->translate(array(
						'data' => array_merge($blog['blog'], $blog['user']),
						'userInfo' => &$userInfo,
						));
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->serverError();
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.status_id_invalid');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.status_id_invalid');
		}		
		
		$this->output();
	}
	
	/**
	 * 6.4 显示消息
	 * 
	 * @return
	 */
	public function showAction()
	{
		$id = $this->id;
		$this->xmlRoot = 'status';
		$userInfo = $this->auth();
		
		if ($id) {
			$comments = (bool)($this->getRequest()->getParam('comments', 'false')=='true' ? true : false);
			
			$blog = Better_Blog::getBlog($id);
			if (isset($blog['user']) && isset($blog['user']) && isset($blog['blog']['uid'])) {
				$this->data[$this->xmlRoot] = $this->api->getTranslator('status')->translate(array(
					'data' => array_merge($blog['user'], $blog['blog']),
					'user' => &$blog['user'],
					'blog' => &$blog['blog'],
					'userInfo' => &$userInfo,
					'comments' => $comments
					));
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.status_id_invalid');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.status_id_invalid');
		}		
		
		$this->output();
	}
	
	/**
	 * 回复
	 * 
	 * @return
	 */
	public function repliesAction()
	{
		$userInfo = $this->auth();
		$id = $this->id;

		$result = Better_Blog::getRepliesByBid($id, $this->page, $this->count);

		foreach ($result['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'status' => $this->api->getTranslator('status')->translate(array(
					'data' => &$row,
					'userInfo' => &$userInfo,
					)),
				);
		}		
		
		$this->output();
	}
	
	/**
	 * 6.2 发表吼吼
	 * 
	 * @return
	 */
	public function updateAction()
	{
		$this->xmlRoot = 'update';

		$this->needPost();
		$this->auth();
		//$this->needSufficientKarma();
		
		if ($this->user->isMuted()) {
			Better_Controller::sendSquidHeaderC(-11);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.user.you_are_muted');
		}

		$status = trim($this->post['content']);
		if ($status=='' && !(is_array($_FILES) && isset($_FILES['photo']))) {
			Better_Controller::sendSquidHeaderC(-10);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.status_required');
		}
		
		$in_reply_to_status_id = $this->post['in_reply_to_status_id'];
		/*if ($in_reply_to_status_id) {
			list($ruid, $foobar) = explode('.', $in_reply_to_status_id);
			if ($ruid==$this->uid) {
				Better_Controller::sendSquidHeaderC(-20);
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.cant_reply_self');
			}
		}*/

		$photo = '';
		if (is_array($_FILES) && isset($_FILES['photo'])) {
			if ($this->post['image_rotate']) {
				$rotates = array(
					'photo' => $this->post['image_rotate']
					);
				Better_Registry::set('image_rotates', $rotates);
			}
			
			$at = Better_Attachment::getInstance('photo');
			$newFile = $at->uploadFile('photo');

			if (is_object($newFile) && ($newFile instanceof Better_Attachment)) {
				$result = $newFile->parseAttachment();
			} else {
				$result = &$newFile;
			}

			if (is_array($result) && $result['file_id']) {
				$photo = $result['file_id'];
			} else if (count(explode('.', $result))==2) {
				$photo = $result;
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				Better_Controller::sendSquidHeaderC($result);
				$this->error('error.status.upload.code_'.$result);
			}
		}
		
		$source = $this->post['source'];
		$address = $this->post['address'];
		$range = floatval($this->post['range']);
		$poiId = Better_Poi_Info::dehashId($this->post['poi_id']);
		$visibility = in_array($this->post['visibility'], array(
			'all', 'friend', 'private'
			)) ? $this->post['visibility'] : 'all';

		if (BETTER_AIBANG_POI && $poiId && !is_numeric($poiId)) {
			$poiId = Better_Service_Aibang_Pool::ab2our($poiId);
		}			

		$bid = $this->user->blog()->add(array(
				'message' => $status,
				'upbid' => $in_reply_to_status_id,
				'real_upbid'=> $this->post['real_in_reply_to_status_id'] ? $this->post['real_in_reply_to_status_id'] : $in_reply_to_status_id,
				'attach' => $photo,
				'source' => $source,
				'address' => $address,
				'range' => $range,
				'poi_id' => $poiId,		
				'priv' => $visibility,
				'checkin_need_sync' => ($this->getRequest()->getParam('sync', 'true')=='false' ? 0 : 1),
				'need_sync' => ($this->getRequest()->getParam('sync', 'true')=='false' ? 0 : 1)
			));
						
		if ($bid==-1) {
			Better_Controller::sendSquidHeaderC($bid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statues.post_need_check');
		} else if ($bid==-2 || $bid==-4) {
			Better_Controller::sendSquidHeaderC($bid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.user.you_are_muted');
		} else if ($bid==-5) {
			Better_Controller::sendSquidHeaderC($bid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.post_too_fast');			
		} else if ($bid==-6) {
			Better_Controller::sendSquidHeaderC($bid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.post_same_content');					
		} else if ($bid==-3) {
			Better_Controller::sendSquidHeaderC($bid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			//$this->lang->error->statuses->post->ban_words_but
			$this->error('error.statuses.post.ban_words_but');
		} else if($bid==-7){
			Better_Controller::sendSquidHeaderC($bid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.post_not_allow_rt');	
		}else {
			Better_Controller::sendSquidHeaderC(1);
			$blog = Better_Blog::getBlog($bid);
			$blog['user']['user_lon'] = $blog['user']['lon'];
			$blog['user']['user_lat'] = $blog['user']['lat'];
			
			$message = $this->parseAchievements($this->langAll->global->this_shout);
			
			$this->data[$this->xmlRoot] = array(
				'message' => $message,
				'status' => $this->api->getTranslator('status')->translate(array(
					'data' => array_merge($blog['blog'], $blog['user']),
					'userInfo' => &$this->userInfo,
					)),
				);
		}		
		
		$this->output();
	}
	
	/**
	 * 收藏相关操作
	 * 6.6 收藏消息
	 * 6.7 删除收藏
	 * 
	 * @return
	 */
	public function favoritesAction()
	{
		$this->xmlRoot = 'status';
		$this->auth();
		$this->needPost();
		$bid = $this->getRequest()->getParam('id', 0);
		
		if ($this->todo=='create') {
		
			if (Better_Blog::validBid($bid)) {
				$data = Better_Blog::getBlog($bid);
	
				if (isset($data['blog']['bid'])) {
					if ($data['blog']['uid']) {
						$f = Better_User_Favorites::getInstance($this->uid)->add($data['blog']['bid'], $data['user']['uid'], $data['blog']['type']);
						if ($f===true) {
							$this->user->push('favorites', $data['blog']['bid']);
							
							$this->data[$this->xmlRoot] = $this->api->getTranslator('status')->translate(array(
								'data' => array_merge($data['user'], $data['blog']),
								'userInfo' => &$this->userInfo
								));
						} else if ($f===0) {
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->error('error.favorites.already_favorite');
						} else {
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->error('error.favorites.failed');
						}
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.favorites.cant_self');
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.favorites.status_not_found');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.favorites.status_id_invalid');
			}		
			
		} else if ($this->todo=='destroy') {
			if (Better_Blog::validBid($bid)) {
				$data = Better_Blog::getBlog($bid);
				if (isset($data['blog']['bid'])) {
					$f = Better_User_Favorites::getInstance($this->uid)->delete($data['blog']['bid']);
					if ($f) {
						$this->user->favorites = array_diff($this->user->favorites, array($data['blog']['bid']));
						
						$this->data[$this->xmlRoot] = $this->api->getTranslator('status')->translate(array(
							'data' => array_merge($data['user'], $data['blog']),
							'userInfo' => &$this->userInfo
							));
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.favorites.status_id_invalid');
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.favorites.status_not_found');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.favorites.status_id_invalid');
			}					
		}
		
		$this->output();

	}
	
	
	/**
	 * 取某条微博评论
	 * 
	 * @return
	 */
	public function commentsAction()
	{
		$this->auth();
		$this->xmlRoot = 'comments';
		
		$bid = $this->getRequest()->getParam('bid', '');
		
		$rows = Better_Blog::getRepliesByBid($bid, $this->page, $this->count);
		
		foreach ($rows as $row) {
			$this->data[$this->xmlRoot][] = array(
				'comment' => $this->api->getTranslator('comment')->translate(array(
					'data' => &$row
					))
				);
		}		
		
		$this->output();
	}
	
	
	/**
	 * 发表评论
	 */
	public function postcommentAction(){
		$this->xmlRoot = 'comment';

		$this->needPost();
		$this->auth();
		//$this->needSufficientKarma();
		
		if ($this->user->isMuted()) {
			Better_Controller::sendSquidHeaderC(-11);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.user.you_are_muted');
		}

		$message = trim($this->post['message']);
		if ($message=='') {
			Better_Controller::sendSquidHeaderC(-10);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.comments_required');
		}
		
		$this->post['to_shout'] = isset($this->post['to_shout']) ? ($this->post['to_shout']=='true'? true : false) : false;
		$this->post['need_sync'] = isset($this->post['need_sync']) ? ($this->post['need_sync']=='true'? true: false) : false;
		
		$nbid = Better_Blog::postReply($this->post['bid'], $this->uid, $this->post);
						
		if ($nbid==-3) {
			Better_Controller::sendSquidHeaderC($nbid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statues.post_need_check');
		} else if ($nbid==-1 || $nbid==-4) {
			Better_Controller::sendSquidHeaderC($nbid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.user.you_are_muted');
		} else if ($nbid==-5) {
			Better_Controller::sendSquidHeaderC($nbid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.post_too_fast');			
		} else if ($nbid==-6) {
			Better_Controller::sendSquidHeaderC($nbid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.post_same_content');					
		} else if ($nbid==-2) {
			Better_Controller::sendSquidHeaderC($nbid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.post.ban_words_but');
		} else if($nbid==-7){
			Better_Controller::sendSquidHeaderC($nbid);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.post_not_allow_rt');
		}else {
			Better_Controller::sendSquidHeaderC(1);
			
			list($blog_uid, $o)= explode('.', $this->post['bid']);
			$row = Better_DAO_Blogreply::getInstance($blog_uid)->get($nbid);
			$user = Better_user::getInstance($row['uid'])->getUser();
			$row['user'] = $user;
			
			$this->data[$this->xmlRoot] = $this->api->getTranslator('comment')->translate(array(
					'data' => &$row
			));
		}		
		
		$this->output();
		
	}
	
	
	//删除评论
	public function delcommentAction(){
		$this->auth();
		$this->xmlRoot = 'comment';
		
		$id = $this->getRequest()->getParam('id', '');
		$bid = $this->getRequest()->getParam('bid', '');
		
		if($id && $bid){
			list($blog_uid, $i) = explode('.', $bid);
			$comment = Better_DAO_Blogreply::getInstance($blog_uid)->get($id);
			if($comment['id']){
				if($this->uid == $comment['uid'] || $this->uid==$blog_uid){
					if(Better_Blog::deleteReply($comment['id'], $bid)){
						$this->data[$this->xmlRoot] = $this->api->getTranslator('comment')->translate(array(
							'data' => &$comment
						));
					}
				}else{
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.comment.not_permission');
				}
			}else{
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.comment.invaild_id');
			}
		}else{
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.comment.invaild_id');
		}
		
		$this->output();
	}
	
}
