<?php

/**
 * 微博相关
 * 
 * @package Controllers
 * @author leip <lei@peptalk.cn>
 *
 */
class Ajax_BlogController extends Better_Controller_Ajax
{
	protected $page = 1;
	
	public function init()
	{
		parent::init();	
		
		$this->page = $this->getRequest()->getParam('page', 1);
	}	
	
	/**
	 * 所有回复
	 * 
	 * @return
	 */
	/*public function repliesAction()
	{
		$uid = $this->getRequest()->getParam('uid', $this->uid);
		$return = Better_Blog::getRepliesByUids(array($uid), $this->page, BETTER_PAGE_SIZE);

		$this->output['rows'] = Better_Output::filterBlogs($return['rows']);
		$this->output['count'] = $return['count'];
		$this->output['pages'] = Better_Functions::calPages($return['count']);
		$this->output['page'] = $this->page;	

		$this->output();
	}*/ 
	
	
	/**
	 * 取微博评论
	 */
	public function repliesAction(){
		$bid = $this->getRequest()->getParam('bid', '');
		$pageSize = $this->getRequest()->getParam('pageSize', 20);
		$only_list = $this->getRequest()->getParam('only_list', false);
		
		$rows = Better_Blog::getRepliesByBid($bid, $this->page, $pageSize);
		list($blog_uid, $i) = explode('.', $bid);
		$count = Better_DAO_Blogreply::getInstance($blog_uid)->getRepliesCount($bid);
		
		$this->output['rows'] = $this->generateCommentsRows($rows, $bid, $count, $only_list);
		$this->output['count'] = $count;
		$this->output['pages'] = Better_Functions::calPages($count);
		$this->output['page'] = $this->page;	
		$this->output();
	}
	
	
	/**
	 * 发布微博评论
	 * 
	 * @return
	 */
	public function postreplyAction()
	{
		$post = $this->getRequest()->getParams();
		
		$small_avatar = isset($post['small_avatar']) ? $post['small_avatar']: 1;
		
		$min = $this->config->blog->post_min_size;
		$max = $this->config->blog->post_max_size*3;
		
		$this->output['code'] = 'failed';
		$this->output['notify'] = '';

		$validator = new Zend_Validate_StringLength($min, $max);
		
		if (BETTER_HASH_POI_ID && $post['poi_id']) {
			$post['poi_id'] = Better_Poi_Info::dehashId($post['poi_id']);
		}
		
		$this->output['uid'] = $this->uid;
		if($this->uid>0){
			if ($validator->isValid($post['message'])) {
				$nbid = Better_Blog::postReply($post['bid'], $this->uid, $post);
				
				if ($nbid==-3) {
					$this->output['nbid'] = -3;
					$this->output['code'] = 'need_check';
				} else if ($nbid==-1 || $nbid==-4) {
					$this->output['nbid'] = -1;
					$this->output['code'] = 'you_r_muted';
				}else if($nbid==-2){
					$this->output['nbid'] = -2;
					$this->output['code'] = 'words_r_banned';
				} else if ($nbid==-5) {
					$this->output['nbid'] = -5;
					$this->output['code'] = 'post_too_fast';
				} else if ($nbid==-6) {
					$this->output['nbid'] = -6;
					$this->output['code'] = 'post_same_content';
				} else if($nbid==-7){
					$this->output['nbid'] = -7;
					$this->output['code'] = 'not_allow_rt';
				}else if ($nbid) {
					$this->output['nbid'] = $nbid;
					$this->output['code'] = 'success';
					$this->output['notify'] = Better_Hook::getNotify('BlogPosted');
					
					//评论成功返回这条
					list($blog_uid, $o)= explode('.', $post['bid']);
					$row = Better_DAO_Blogreply::getInstance($blog_uid)->get($nbid);
					$user = Better_user::getInstance($row['uid'])->getUser();
					$row['user'] = $user;
					$rows = array($row);
					$this->output['msg'] = $this->generateCommentsRows($rows, $post['bid'], 0, true, $small_avatar);
				} else {
					$this->output['msg'] = 'insert failed';
				}
			} else{
				$this->output['msg'] = 'valid failed';
			}	
		}	

		$this->output();
	}
	
	
	/**
	 * 删除一条评论
	 */
	public function delreplyAction(){
		$id = $this->getRequest()->getParam('id', '');
		$bid = $this->getRequest()->getParam('bid', '');
		
		$this->output['code'] = 0;
		
		if($id && $bid){
			list($blog_uid, $i) = explode('.', $bid);
			$comment = Better_DAO_Blogreply::getInstance($blog_uid)->get($id);
			if($comment){
				if($this->uid == $comment['uid'] || $this->uid==$blog_uid){
					$this->output['code'] = Better_Blog::deleteReply($comment['id'], $bid);
				}else{
					$this->output['code'] = -1;
				}
			}
		}
		
		$this->output();
	}
	
	
	/**
	 * 收藏一条微博
	 * 
	 * @return
	 */
	public function favoriteAction()
	{
		$bid = $this->getRequest()->getParam('bid', '');
		if (Better_Blog::validBid($bid)) {
			$data = Better_Blog::getBlog($bid);

			$type = $this->getRequest()->getParam('type', 'normal');
			
			if (isset($data['blog']['bid'])) {
				$f = Better_User_Favorites::getInstance($this->uid)->add($data['blog']['bid'], $data['user']['uid'], $type);
				if ($f) {
					$this->output['favorited_bid'] = $data['blog']['bid'];
					$this->output['data'] = self::$SUCCESS;
				} else {
					$this->error(self::$DATA_ERROR);
				}
			} else {
				$this->error(self::$INVALID_DATA);
			}
		} else {
			$this->error(self::$INVALID_DATA);
		}		
		
		$this->output();
	}
	
	/**
	 * 取消收藏一个微博
	 * 
	 * @return
	 */
	public function unfavoriteAction()
	{
		$bid = $this->getRequest()->getParam('bid', '');
		if (Better_Blog::validBid($bid)) {
			$data = Better_Blog::getBlog($bid);

			if (isset($data['blog']['bid'])) {
				$f = Better_User_Favorites::getInstance($this->uid)->delete($data['blog']['bid']);
				if ($f) {
					$this->output['unfavorited_bid'] = $data['blog']['bid'];
					$this->output['data'] = self::$SUCCESS;
				} else {
					$this->error(self::$DATA_ERROR);
				}
			} else {
				$this->error(self::$INVALID_DATA);
			}
		} else {
			$this->error(self::$INVALID_DATA);
		}		
		
		$this->processRightbar();
		
		$this->output();
	}
	
	/**
	 * 列出所有收藏
	 * 
	 * @return
	 */
	public function favoritesAction()
	{
		$type = $this->getRequest()->getParam('type', 'normal')=='tips' ? 'tips' : 'normal';
		$nickname = $this->getRequest()->getParam('nickname');
		
		$user = Better_User::getInstance();
		$info = $user->getUserByNickname($nickname);
		$user = Better_User::getInstance($info['uid']);
		
		if ($type=='normal') {
			$return = $user->favorites()->all($this->page, BETTER_PAGE_SIZE, array('normal', 'checkin','todo'));
		} else {
			$return = $user->favorites()->allTips($this->page, BETTER_PAGE_SIZE);
		}
		
		$this->output['rows'] = Better_Output::filterBlogs($return['rows']);
		$this->output['count'] = $return['count'];
		$this->output['pages'] = Better_Functions::calPages($return['count']);
		$this->output['page'] = $this->page;	
		$this->output['rts'] = Better_Output::filterBlogs($return['rts']);	
		
		$this->output();
	}
	
	public function opratetodoAction()
	{
		$result = 0;
		$bid = trim($this->getRequest()->getParam('bid')); //175623.2593
		$status = $this->getRequest()->getParam('status',"nottodo"); // type
		if (preg_match('/^([0-9]+).([0-9]+)$/is',$bid)) {
			list($uid, $cnt) = explode('.', $bid);
			if (strlen($uid) && strlen($cnt) && $uid==$this->userInfo['uid']) {
				if($status == "beenhere"){ //已去过
					$result = Better_Blog::beentodo($bid);
				}else {//不想去了
					$result = Better_Blog::canceltodo($bid);
				}
			}
		}
		$this->output['bid'] = $bid;
		$this->output['result'] = $result>0?1:0;		
		$this->processRightbar();		
		$this->output();
	}
	/**
	 * 删除一个微博
	 * 
	 * @return
	 */
	public function deleteAction()
	{
		$result = 0;
		$bid = trim($this->getRequest()->getParam('bid')); //175623.2593
		
		if (preg_match('/^([0-9]+).([0-9]+)$/is',$bid)) {
			list($uid, $cnt) = explode('.', $bid);
			if (strlen($uid) && strlen($cnt) && $uid==$this->userInfo['uid']) {
				$result = Better_Blog::delete($bid);
			}
//			if ($type!='cancel_todo' && strlen($uid) && strlen($cnt) && $uid==$this->userInfo['uid']) {
//				$result = Better_Blog::delete($bid);
//			}else{
//				$blog = Better_DAO_Blog::getInstance($uid)->get($bid);					
//				$bids = Better_DAO_Blog::getInstance($this->userInfo['uid'])->getBids($this->userInfo['uid'],$blog['poi_id']);
//				foreach($bids as $b){
//					$result= Better_Blog::delete($b['bid'],$status);
//				}				
//				if($result){					
//					$msg = '用户【'.$this->userInfo['uid'].'】取消了地点【'.$blog['poi_id'].'】想去计划。';
//					Better_Log::getInstance()->log($msg,'user_poi_todo');
//				}
//			}
		}

		$this->output['bid'] = $bid;
		$this->output['result'] = $result;
		
		$this->processRightbar();
		
		$this->output();
	}
	
	/**
	 * 微博列表
	 * 
	 * @return
	 */
	public function listAction()
	{
		define('BETTER_BLOG_LIST_CASE', 'list_follow');
		$return = array();

		$userObj = $this->uid ? $this->user : Better_User::getInstance();
		
		
		$return = $userObj->status()->webFollowings(array(
			'page' => $this->page,
			'is_following' => true,
			'type' => array('normal', 'checkin', 'tips'),
			'page_size' => BETTER_PAGE_SIZE		
			));			


		$this->output['rows'] = Better_Output::filterBlogs($return['rows']);
		$this->output['count'] = $return['count'];
		$this->output['rts'] = Better_Output::filterBlogs($return['rts']);
		$this->output['pages'] = count($this->output['rows']) ? Better_Functions::calPages($return['count']) : count($this->output['rows']);
		$this->output['page'] = $this->page;		

		$this->output();
	}
	
	/**
	 * 列出我的及关注的微博
	 * 
	 * @return
	 */
	public function listmineAction()
	{
		$nickname = $this->getRequest()->getParam('nickname');
		$user = Better_User::getInstance();
		$userInfo = $user->getUserByNickname($nickname);
		$user = Better_User::getInstance($userInfo['uid']);
		
		$userObj = $this->uid ? $this->user : Better_User::getInstance(BETTER_VIRTUAL_UID);
		
		if ($userObj->canViewDoing($userInfo['uid'])) {
			$return = $userObj->status()->getSomebody(array(
				'page' => $this->page,
				'page_size' => BETTER_PAGE_SIZE,
				'uid' => $userInfo['uid'],
				'ignore_block' => true
				));
		} else {
			$return = array(
				'rows' => array(),
				'count' => 0,
				'pages' => 0,
				'rts' => array()
				);
		}

		$this->output['rows'] = Better_Output::filterBlogs($return['rows']);
		$this->output['count'] = $return['count'];
		$this->output['pages'] = Better_Functions::calPages($return['count']);
		$this->output['page'] = $this->page;		
		$this->output['rts'] = Better_Output::filterBlogs($return['rts']);
		
		$this->output();
	}
	
/**
	 * 列出我被转发的微博
	 * 
	 * @return
	 */
	public function listrtmineAction()
	{
		$user = Better_User::getInstance();		
		
		$return = $this->user->status()->webMetionMe(array(
			'page' => $this->page,
			'page_size' => BETTER_PAGE_SIZE
			));
		
		if ($this->uid) {
			$this->user->updateUser(array('last_rt_mine'=>time()));
			$this->user->cache()->set('last_rt_got', 0, 300);
			$this->user->cache()->set('rt_blogs_count', 0);
		}
		
		$this->output['rows'] = Better_Output::filterBlogs($return['rows']);
		$this->output['count'] = $return['count'];
		$this->output['pages'] = Better_Functions::calPages($return['count']);
		$this->output['page'] = $this->page;		
		$this->output['rts'] = Better_Output::filterBlogs($return['rts']);
		
		$this->output();
	}
	/**
	 * 列出所有
	 * 
	 * @return
	 */
	public function listallAction()
	{
		define('BETTER_BLOG_LIST_CASE', 'list_all');
		$return = array();
		
		if ($this->config->join_public) {
			$return = $this->uid ? $this->user->blog()->getAllPublic(array(
				'page' => $this->page
				)) : array(
					'rows' => array(),
					'count' => 0,
					'rts' => array()
				);
		} else {
			if ($this->page==1 && $this->uid) {
				$return = $this->user->cache()->get('index_listall');
			}
	
			if (count($return)<=1) {
				$karmaLimit = (int)$this->config->public_karma_limit;
				$userObj = $this->uid ? $this->user : Better_User::getInstance();
				$return = $this->user->blog()->getAllBlogs(array(
					'page' => $this->page,
					'type' => array('normal', 'checkin', 'tips')
					), BETTER_PAGE_SIZE, $karmaLimit);
			}
		}

		$this->output['rows'] = Better_Output::filterBlogs($return['rows']);
		$this->output['count'] = $return['count'];
		$this->output['pages'] = Better_Functions::calPages($return['count']);
		$this->output['page'] = $this->page;		
		$this->output['rts'] = Better_Output::filterBlogs($return['rts']);
		
		$this->output();
	}
	
	/**
	 * 
	 * 我的周围
	 */
	public function aroundmeAction()
	{
		$page = $this->getRequest()->getParam('page',1);
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		
		$data = array(
			'rows' => array(),
			'count' => 0,
			'rts' => array()
			);
			
		$userObj = $this->uid ? $this->user : Better_User::getInstance();
		$data = $userObj->status()->webAroundMe(array(
			'page' => $page,
			'page_size' => $count
			));

		$this->output['rows'] = Better_Output::filterBlogs($data['rows']);
		$this->output['pages'] = Better_Functions::calPages($data['count'], $count);
		$this->output['page'] = $page;
		$this->output['count'] = &$data['count'];
		$this->output['rts'] = &Better_Output::filterBlogs($data['rts']);
		
		$this->output();				
	}
	
	/**
	 * 搜索qbs 
	 * 
	 * @return
	 */
	public function searchqbsAction()
	{
		$lon = $this->getRequest()->getParam('lon');
		$lat = $this->getRequest()->getParam('lat');
		$w = $this->getRequest()->getParam('w', $this->qbsDefaultW);
		$h = $this->getRequest()->getParam('h', $this->qbsDefaultH);

		$withPhoto = $this->getRequest()->getParam('withPhoto', 0);
		$keyword = $lon.'|'.$lat.'|'.$w.'|'.$h.'|'.$withPhoto;

		$result = Better_Search::factory(array(
			'what' => 'blog',
			'lon' => $lon,
			'lat' => $lat,
			'w' => $w,
			'h' => $h,
			'with_photo' => $withPhoto,
			'method' => 'qbs',
			'page' => $this->page,
			'count' => BETTER_PAGE_SIZE
			))->search();

		$this->output['page'] = $this->page;
		$this->output['rows'] = Better_Output::filterBlogs($result['rows']);
		$this->output['count'] = $result['count'];
		$this->output['pages'] = $result['pages'];
		$this->output['rts'] = Better_Output::filterBlogs($result['rts']);

		$this->output();
	}
	
	/**
	 * 搜索微博
	 * 
	 * @return
	 */
	public function searchAction()
	{
		$karmaLimit = (int)$this->getRequest()->getParam('karma_limit', 0);
		$karmaLimit && $karmaLimit = (int)$this->config->public_karma_limit;
		$text = $this->getRequest()->getParam('search_text', '');
		$poiId = (int)Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$searchRange = trim($this->getRequest()->getParam('search_range', 'normal'));
		$searchRange=='blog' && $searchRange = 'normal';

		$this->output['page'] = $this->page;
		$this->output['pages'] = 0;
		$this->output['rows'] = array();
		$this->output['count'] = 0;

		if ($searchRange=='normal' || $searchRange=='') {
			$range = $searchRange=='normal' ? 'normal' : array('normal', 'checkin');
			
			$params = array(
				'keyword' => $text,
				'page' => $this->page,
				'page_size' => BETTER_PAGE_SIZE,
				'poi' => $poiId,
				'type' => $range,
				'without_me' => (bool)$this->getRequest()->getParam('without_me', 0),
				);
			$lon = $this->getRequest()->getParam('lon', '');
			$lat = $this->getRequest()->getParam('lat', '');
			
			if ($lon && $lat) {
				$range = (float)$this->getRequest()->getParam('range', 5000);
				$params['lon'] = (float)$lon;
				$params['lat'] = (float)$lat;
				$params['range'] = $range;
			}

			$result = $this->uid ? $this->user->blog()->getAllBlogs($params, BETTER_PAGE_SIZE, $karmaLimit) : Better_User::getInstance()->blog()->getAllBlogs($params, BETTER_PAGE_SIZE, $karmaLimit);

			$this->output['count'] = $result['count'];
			$this->output['pages'] = $result['pages'];
			$this->output['rows'] = Better_Output::filterBlogs($result['rows']);
			$this->output['rts'] = Better_Output::filterBlogs($result['rts']);			
		} else if ($searchRange=='tips') {
			$result = Better_Search::factory(array(
				'what' => 'blog',
				'keyword' => $text,
				'method' => 'mysql',
				'page' => $this->page,
				'count' => BETTER_PAGE_SIZE,
				'poi_id' => $poiId,
				'type' => $searchRange,
				))->search();

			$this->output['count'] = $result['count'];
			$this->output['pages'] = $result['pages'];
			$this->output['rows'] = Better_Output::filterBlogs($result['rows']);
			$this->output['rts'] = Better_Output::filterBlogs($result['rts']);			
		}
		
		$this->output();
	}
	
	/**
	 * 发布微博
	 * 
	 * @return
	 */
	public function postAction()
	{
		$post = $this->getRequest()->getPost();
		
		$min = $this->config->blog->post_min_size;
		$max = $this->config->blog->post_max_size*3;
		
		$this->output['code'] = 'failed';
		$this->output['notify'] = '';

		$validator = new Zend_Validate_StringLength($min, $max);
		
		if (BETTER_HASH_POI_ID && $post['poi_id']) {
			$post['poi_id'] = Better_Poi_Info::dehashId($post['poi_id']);
		}
		
		if($post['map_url']){
			$post['map_url'] = $post['attach']? '': $post['map_url'];
			if($post['map_url']){
				$post['attach'] = Better_Attachment_Save::getInstance('map_url')->uploadImgLink($post['map_url'], 'png');
			}
		}

		$this->output['uid'] = $this->uid;
		$upbid = $post['upbid'];
		if($this->uid>0){
			if($post['attach'] == Better_Attachment_Save::$IMG_LINK_WRONG){
				$this->output['code'] = 'link_wrong';				
			}else if ($upbid || $post['attach'] || $validator->isValid($post['message'])) {
				
				$nbid = Better_User_Blog::getInstance($this->uid)->add($post);
	
				if ($nbid==-1) {
					$this->output['nbid'] = -1;
					$this->output['code'] = 'need_check';
				} else if ($nbid==-2 || $nbid==-4) {
					$this->output['nbid'] = -2;
					$this->output['code'] = 'you_r_muted';
				}else if($nbid==-3){
					$this->output['nbid'] = -3;
					$this->output['code'] = 'words_r_banned';
				} else if ($nbid==-5) {
					$this->output['nbid'] = -5;
					$this->output['code'] = 'post_too_fast';
				} else if ($nbid==-6) {
					$this->output['nbid'] = -6;
					$this->output['code'] = 'post_same_content';
				} else if($nbid==-7){
					$this->output['nbid'] = -7;
					$this->output['code'] = 'not_allow_rt';
				} else if ($nbid) {
					$this->output['nbid'] = $nbid;
					$this->output['code'] = 'success';
					$this->output['notify'] = Better_Hook::getNotify('BlogPosted');
				} else {
					$this->output['msg'] = 'insert failed';
				}
			} else if (!$post['attach']) {
				$this->output['msg'] = 'valid failed';
			} else if (!$post['attach'] && !$validator->isValid($post['message'])) {
				$this->output['code'] = 'too_short';
			}	
		}	

		$this->output();
	}
	
	
	public function posttodoAction()
	{
		$post = $this->getRequest()->getPost();			
		$this->output['code'] = 'failed';
		$this->output['notify'] = '';		
		if (BETTER_HASH_POI_ID && $post['poi_id']) {
			$post['poi_id'] = Better_Poi_Info::dehashId($post['poi_id']);
		}
		
		$this->output['uid'] = $this->uid;
		if($this->uid>0){
				$nbid = Better_User_Blog::getInstance($this->uid)->add($post);	
				if ($nbid==-1) {
					$this->output['nbid'] = -1;
					$this->output['code'] = 'need_check';
				} else if ($nbid==-2 || $nbid==-4) {
					$this->output['nbid'] = -2;
					$this->output['code'] = 'you_r_muted';
				}else if($nbid==-3){
					$this->output['nbid'] = -3;
					$this->output['code'] = 'words_r_banned';
				} else if ($nbid==-5) {
					$this->output['nbid'] = -5;
					$this->output['code'] = 'post_too_fast';
				} else if ($nbid==-6) {
					$this->output['nbid'] = -6;
					$this->output['code'] = 'post_same_content';
				} else if($nbid==-7){
					$this->output['nbid'] = -7;
					$this->output['code'] = 'not_allow_rt';
				} else if ($nbid) {
					$this->output['nbid'] = $nbid;
					$this->output['code'] = 'success';
					$this->output['notify'] = Better_Hook::getNotify('BlogPosted');
				} else {
					$this->output['msg'] = 'insert failed';
				}
		}	

		$this->output();
	}
	
	/**
	 * 反向解析微博
	 * 
	 * @deprecated
	 * @return
	 */
	public function reverseAction()
	{
		$post = $this->getRequest()->getPost();
		$msg = $post['msg'];
		$nickname = $post['nickname'];
		$attach = $post['attach'];
		if ($attach) {
			$at = Better_Attachment::getInstance($attach);
			$data = $at->parseAttachment();
			if (!isset($data['file_id']) || !$data['file_id']) {
				$attach = '';
			}
		}
		
		$this->output['msg'] = Better_Blog::reverseMessage($msg);
		$this->output['nickname'] = $nickname;
		$this->output['attach'] = $attach;		
		
		$this->output();
	}
	
	
	/**
	 * 更新最后一次读取rt mine的时间
	 */
	public function updatelastrtmineAction(){
		if ($this->uid) {
			$this->user->updateUser(array('last_rt_mine'=>time()));
			$this->user->cache()->set('last_rt_got', 0, 300);
		}
		$this->output['result'] = 1;
		
		$this->output();
	}
	
	
	
	/**
	 * 评论列表生成
	 */
	private function generateCommentsRows($rows, $bid, $count, $only_list=false, $small_avatar=0){
		list($blog_uid, $i) = explode('.', $bid);
		
		$list = '';
		if($only_list && !$small_avatar){
			$avatar_width = '48';
		}else{
			$avatar_width = '35';
		}
		foreach($rows as $row){
			$del_btn = '';
			if($this->uid==$row['uid'] || $this->uid==$blog_uid){
				$del_btn = "<a href='javascript: void(0);' class='right' onclick='Better_Comment_delete(\"{$row['id']}\", \"{$bid}\", $(this));' style='margin-right: 10px;'>删除</a>";
			}
			$message = Better_Blog::dynFilterMessage($row['message']);
			$message = Better_Blog::parseBlogAt($message);
			$message = html_entity_decode($message);
			
			$time = Better_Functions::compareTime($row['dateline']);
			$list .= "
				<div class='row'>
					<div class='avatar'>
						<img src='{$row['user']['avatar_small']}' width='{$avatar_width}'/>
					</div>
					<div class='msg'>
						<p><span class='username'><a href='/{$row['user']['username']}'>{$row['user']['nickname']}</a></span>&nbsp;{$message}</p>
						<span class='time left'>{$time}</span>
						{$del_btn}
						<a href='javascript: void(0);' class='right' onclick='Better_Comment_reply($(this));' style='margin-right: 10px;'>回复</a>
						<div class='clearfix'></div>
					</div>
				</div>
			";
		}
		
		$blog = Better_User_Status::getInstance($this->uid)->byBid($bid);
		if($blog['allow_rt']){
			$to_shout_btn = "<input type='checkbox' class='to_shout' onclick='switch_sync($(this));'>同时发布到吼吼<br>";
			
			$syncSites = (array)Better_User_Syncsites::getInstance($this->uid)->getSites();
			if(count($syncSites)>0){
				$share_btn = "<span class='hide'><input type='checkbox' class='sync'>分享到我已绑定的社交网络</span>";
			}else{
				$share_btn = "";
			}
		}else{
			$to_shout_btn = $blog['nickname'].' 的动态不允许被转发';
			$share_btn = "";
		}
		
		$look1 = $look2 = '';
		$count!=0 && $look1 = "，<a href='/comments?bid={$bid}'>点击查看&gt;&gt;</a>";
		$count>10 && $look2 = "共{$count}条评论，<a href='/comments?bid={$bid}'>点击查看&gt;&gt;</a>";
		
		$return = <<<EOF
<div class='comment'>
				<div class='incomment'>
					<div class='post_comment'>
						<div>
							<span class='left'>还可以输入<span class='remain_count'>140</span>个字</span>
							<span class='right'>共{$count}条评论{$look1}</span>
							<div class='clearfix'></div>
						</div>
						<textarea style='width: 553px; height: 35px; overflow-x: hidden; margin:8px 0;' onkeyup='Better_Commment_setRemainCounts($(this));' onmousedown='Better_Commment_setRemainCounts($(this));'></textarea>
						<div>
							<span class='left'>
								{$to_shout_btn}
								{$share_btn}
							</span>
							<a class='comment_btn button right' href='javascript: void(0);' onclick='Better_postComment(this, "{$bid}");'>发表评论</a>
							<div class='clearfix'></div>
						</div>
					</div>
					
					<div class='comments_list'>
						{$list}
					</div>
					<span class='right'>{$look2}</span>
					<div class='clearfix'></div>
				</div>
			</div>		
	
EOF;

		if($only_list){
			return $list;
		}else{
			return $return;
		}
	
	}
	
	
}
