<?php

/**
 * 取用户动态重新封装
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_User_Status extends Better_User_Base
{
	protected static $instance = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
	/**
	 * 
	 * 在某个poi想来的好友
	 * @param array $params
	 * @return array
	 */
	public function &getFriendstodo(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array()
			);
		$params['uid'] = $this->uid;		
		$rows = Better_DAO_User_Status::getInstance($this->uid)->getFriendstodo($params);//取得想来这里的好友		
		if (count($rows)>0) {
			$return['count'] = count($rows);
			foreach ($rows as $k=>$r) {
				$return['rows'][$k] = Better_User::getInstance($r->uid)->parseUser($r, false, false, true);
			}
		}
		return $return;
	}
	
	
	/**
	 * 
	 * 在某个poi的签到
	 * @param array $params
	 * @return array
	 */
	public function &getSomePoi(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,		
			);
		
		$rows = Better_DAO_User_Status::getInstance($this->uid)->getSomePoi($params);
		
		if (count($rows)>0) {

			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();
			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}

			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->byBids(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));			

				foreach ($uprows as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}			
		}
				
		return $return;
	}
	
	/**
	 * 提到我的
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &rtMine(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);

		$rows = Better_DAO_User_Status::getInstance($this->uid)->rtMine($params);
		if (count($rows)>0) {

			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();
			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}

			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->byBids(array(
					'bids' => $upbids
					));			

				foreach ($uprows as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}			
		}
		
		return $return;						
	}
	
	/**
	 * 客户端的好友动态
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &apiPublicTimeline(array $params)
	{
		if (Better_Config::getAppConfig()->newpublictimeline) {
			return $this->newWebFollowings($params);
		}
				
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);
			
		$rows = Better_DAO_User_Status::getInstance($this->uid)->apiPublicTimeline($params);
		if (is_array($rows) && count($rows)) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();

			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->byBids(array(
					'bids' => $upbids
					));

				foreach ($uprows as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}						
		}

		return $return;
	}//-- end of method `apiPublicTimeline`
	
	/**
	 * 客户端的“新晋掌门”
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &apiNearby(array $params)
	{
		$return = &$this->user->blog()->getAllPublic($params);
		
		return $return;
	}
	
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function getFriendStatus($params)
	{
		$uid = $params['uid'];
		$pageSize = $params['page_size'];
		$page = $params['page'];
		
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);	
		
		$fids = Better_User_Friends::getInstance($uid)->getFriendsWithHomeShow();
			
		$cacher = Better_Cache::remote();
		$cache_key = 'friends_' . md5(serialize($fids));
		
		$rows = $cacher->get($cache_key);
		if ( !$rows && $rows !== array()) {
			$rows = Better_DAO_User_Status::getInstance($this->uid)->getFriendStatus($fids);
			$r = $cacher->set($cache_key, $rows, 300);
		}
		
		if (count($rows) > $page * $pageSize) {
			$return['pages'] = $params['page'] + 1;
		} else {
			$return['pages'] = $params['page'];
		}
		
		
		$start = ($page - 1 ) * $pageSize;
		$data = array_slice($rows, $start, $pageSize);
		
		$upbids = array();
		foreach ($data as $v) {
			if ($v['upbid']!='0') {
				$upbids[] = $v['upbid'];
			}
			$return['rows'][] = Better_Blog::parseBlogRow($v);
			if (count($return['rows']) == $params['page_size']) break;
		}
		
		if (count($upbids)>0) {
			$upbids = array_unique($upbids);
			$uprows = $this->byBids(array(
				'bids' => $upbids
				));

			foreach ($uprows as $row) {
				$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
			}
		}	

		$return['count'] = count($return['rows']);
		
		return $return;
	}
	
	
	/**
	 * 获取某人的动态
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &getSomebody(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);
		
		// 陌生人只能看贴士,并且开放给发送过加好友请求的人。 updated on:2011-06-20 by Guoyimin
		$uid = (int)$params['uid'];
		if ($uid != $this->uid && $uid!=BETTER_SYS_UID && !$this->user->isFriend($uid) && !$this->user->friends()->hasRequestToMe($uid)) {
			if (count($params['type'])>0 && !in_array('tips', $params['type'])) {
				return $return;
			} else {
				$params['type'] = array('tips');
			}
		}
		$rows = Better_DAO_User_Status::getInstance($this->uid)->getSomebody($params, true);

		if (is_array($rows) && count($rows)) {
			$return['cnt'] = (int)$rows['cnt'];
			unset($rows['cnt']);
			$return['count'] = count($rows);
			// 还有下一页时会多返回一条记录
			if ($return['count'] > $params['page_size']) {
				$return['pages'] = $params['page'] + 1;
			} else {
				$return['pages'] = $params['page'];
			}

			$upbids = array();
			foreach ($rows as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
				if (count($return['rows']) == $params['page_size']) break;
			}
			
			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->byBids(array(
					'bids' => $upbids
					));

				foreach ($uprows as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}						
		}
		return $return;
	}//-- end of method `getSomebody`
	
	public function &getSomeTodo(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);
		
		$rows = Better_DAO_User_Status::getInstance($this->uid)->getSomeTodo($params,$this->uid);
		if (is_array($rows) && count($rows)) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();

			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->byBids(array(
					'bids' => $upbids
					));

				foreach ($uprows as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}						
		}
		return $return;
	}
	
	/**
	 * 
	 * 新版网站“好友动态”
	 * @param array $params
	 */
	public function &newWebFollowings(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0
			);
			
		$page = $params['page'] ? (int)$params['page'] : 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		
		$test_uids = explode(',', Better_Config::getAppConfig()->test->uids);
		if ( Better_Config::getAppConfig()->friendstatus->open && ( in_array($this->uid, $test_uids) || APPLICATION_ENV !='production' ) ) {
			$rows = Better_DAO_User_Friendstatus::getInstance($this->uid)->getMine(array('page' => $page, 'page_size' => $pageSize));
		} else {
			$rows = Better_DAO_User_Publictimeline::getInstance($this->uid)->getMine(array('page' => $page, 'page_size' => $pageSize));
		}

		$return['count'] = $page*$pageSize+1;
		if (is_array($rows) && count($rows)) {
			foreach ($rows as $bid) {
				$data = $this->byBid($bid);

				if ($data['bid']) {
					$return['rows'][] = $data;
					if ($data['upbid']!='0' && !isset($return['rts'][$data['upbid']])) {
						$data2 = $this->byBid($data['upbid']);
						if ($data2['bid']) {
							$return['rts'][$data['upbid']] = $data2;
						}
					}
				}
			}
		}
		
			
		return $return;
	}
	
	public function newWebFollowingsCount()
	{
		$cacher = Better_Cache::remote();
		$cacheKey = md5('kai_blog_bid_count_'.$this->uid);
		$count = $cacher->get($cacheKey);
		if ($count===false) {
			$sum = Better_DAO_User_Publictimeline::getInstance($this->uid)->summary();
			$count = (int)$sum['total'];
			$cacher->set($cacheKey, $count);
		}
		
		return $count;
	}
	
	public function byBid($bid)
	{
		$result = array();
		
		if ($bid) {
			$cacher = Better_Cache::remote();
			$cacheKey = md5('kai_blog_bid_'.$bid);
			
			$result = $cacher->get($cacheKey);
			
			if (!$result) {
				$data = Better_DAO_User_Status::byBid($bid);	
				
				if ($data['bid']) {
					$result = Better_Blog::parseBlogRow($data);
					$cacher->set($cacheKey, $result, 300);
				} else {
					//Better_DAO_User_Publictimeline::clean($bid);
				}
			} else if (!$result['bid']) {
				$result = array();
				//Better_DAO_User_Publictimeline::clean($bid);
				$cacher->set($cacheKey, null);
			}
			
			if ($result['bid']) {
				$flag = false;
				
				if ($result['uid']==$this->uid) {
					$flag = true;
				} else if ($result['state']!=Better_User_State::BANNED) {
					/*if ($result['priv']=='protected') {
						if ($this->user->isFriend($result['uid'])) {
							$flag = true;
						}
					} else if($result['priv']=='private'){
						$flag = false;
					}else {
						$flag = true;
					}*/
					if($result['priv']=='private'){
						$flag = false;
					} else {
						$flag = true;
					}
				}
				
				if ($flag==false) {
					$result = array();
				}		
			}	
		}
		
		return $result;
	}
	
	/**
	 * 网站的“我关注的”=》好友动态
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &webFollowings(array $params)
	{
		if (Better_Config::getAppConfig()->newpublictimeline) {
			return $this->newWebFollowings($params);
		}
		
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);
			
		$rows = Better_DAO_User_Status::getInstance($this->uid)->webFollowings($params);
		if (is_array($rows) && count($rows)) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();

			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->byBids(array(
					'bids' => $upbids
					));

				foreach ($uprows as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}						
		}

		return $return;
	}
	
	/**
	 * 网站“我的周围”
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &webAroundMe(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);

		$this->getUserInfo();
		$params['lon'] = $this->userInfo['lon'];
		$params['lat'] = $this->userInfo['lat'];
		
		$rows = Better_DAO_User_Status::getInstance($this->uid)->aroundSomebody($params);
		
		if (is_array($rows) && count($rows)) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();

			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->byBids(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));

				foreach ($uprows as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}						
		}

		return $return;
	}
	
	/**
	 * 网站“提到我的”
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &webMetionMe(array $params)
	{
		$return = array(
			'rows' => array(),
			'count' => 0,
			'rts' => array()
			);
			
		if ($this->uid) {
			//$return = &$this->rtMine($params);
			$result = &$this->mentionMe($params);
			$return['count'] = $result['count'];
			
			$upbids = $result['upbids'];
			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->byBids(array(
					'bids' => $upbids
					));			

				foreach ($uprows as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}	

			foreach($result['rows'] as $row){
				if(isset($row['comment_id'])){
					$return['rows'][] = array(
						'comment'=>true,
						'content'=>$this->generateCommentRow($row)
					);
				}else{
					$return['rows'][] = $row;
				}
			}
		}
		
		return $return;		
	}
	
	/**
	 * 网站“新晋掌门”
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &webLastestMajor(array $params)
	{
		
	}

	/**
	 * 附近的好友签到
	 * @param array $params
	 */
	public function &friendBlogAroundMe(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			'pages' => 0,
			);
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page <= 0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize <= 0 && $pageSize = BETTER_PAGE_SIZE;

		$rows = Better_DAO_User_Status::getInstance($this->uid)->friendBlogAroundMe($params);

		if (is_array($rows) && count($rows)) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $pageSize);
			$tmp = isset($data[$page-1]) ? $data[$page-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();

			foreach ($tmp as $v) {
				if ($v['upbid']!='0') {
					$upbids[] = $v['upbid'];
				}
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
			
			if (count($upbids)>0) {
				$upbids = array_unique($upbids);
				$uprows = $this->byBids(array(
					'bids' => $upbids,
					'page' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'count' => count($upbids)
					));

				foreach ($uprows as $row) {
					$return['rts'][$row['bid']] = Better_Blog::parseBlogRow($row);
				}
			}						
		}

		return $return;
	}

	/**
	 * 根据bids查询结果
	 * 
	 * @param array $params
	 * @return array
	 */
	public function &byBids(array $params)
	{
		$results = array();
		$bids = (array)$params['bids'];
		
		foreach ($bids as $bid) {
			$data = $this->byBid($bid);
			if ($data['bid']) {
				$results[$data['dateline'].'.'.$data['uid']] = $data;
			}
		}
		
		krsort($results);
		
		return $results;
	}
	
	
	
	/**
	 * 新版‘提到我的’
	 * 
	 * @param array $params
	 * @return array
	 */
	public function mentionMe($params){
		$return = array(
			'count' => 0,
			'rows' => array(),
			'upbids' => array()
			);

		//导入旧的提到我的
		/*if(!$this->user->cache()->get('had_got_old_mentions')){
			$rows = Better_DAO_User_Status::getInstance($this->uid)->simpleRtMine(array(
				'page'=> 10,
				'page_size'=> BETTER_PAGE_SIZE
			));
			
		}*/	
			
		$result = Better_DAO_Mentionme::getInstance($this->uid)->getMentionsByUid(array(
			'uid'=>$this->uid,
			'page'=>$params['page'],
			'page_size'=>$params['page_size']
		));
		
		$return['count'] = $result['count'];
		
		if (count($result['rows'])>0) {
			$upbids = array();
			foreach($result['rows'] as $row){
				if(!$row['comment_id']){
					$bid = $row['bid'];
					$blog = $this->byBid($bid);
					$return['rows'][] = Better_Blog::parseBlogRow($blog);
					
					if($blog['upbid']!='0'){
						$upbids[] = $blog['upbid'];
					}
				}else{
					$comment_id = $row['comment_id'];
					$bid = $row['bid'];
					list($blog_uid, $i) = explode('.', $bid);
					$comment = Better_DAO_Blogreply::getInstance($blog_uid)->get($comment_id);
					if($comment['uid']){
						$user = Better_user::getInstance($comment['uid'])->getUser();
						$comment['user'] = $user;
						$comment['comment_id'] = $comment_id;
					}
					$return['rows'][] = $comment;
				}
			}
			
			$return['upbids'] = $upbids;
		}
		
		return $return;		
	}
	
	
	/**
	 * 评论列表生成
	 */
	private function generateCommentRow($row){
		$blog_uid = $row['blog_uid'];
		$bid = $row['bid'];
		$blog = $this->byBid($bid);
		if($blog['type']=='checkin'){
			$type='签到';
			$blog['message'] = '在 '.$blog['poi']['city'].' '.$blog['poi']['name'].' 签到'.($blog['message']? '：'.$blog['message'] : '');
		}else if($blog['type']=='tips'){
			$type='贴士';
		}else{
			$type='吼吼';
		}
		if ($blog['message'] == '') {
			$blog['message'] = $blog['attach'] ? '上传一张新图片' : ($blog['upbid'] ? Better_Language::load()->javascript->global->blog->rt : '');
		}
		$old_msg = Better_Cutstring::mystrcut($blog['message'], 30);
		
			$del_btn = '';
			if($this->uid==$row['uid'] || $this->uid==$blog_uid){
				$del_btn = "<a href='javascript: void(0);' class='right' onclick='Better_Comment_delete(\"{$row['id']}\", \"{$bid}\", $(this), true);' style='margin-right: 15px; color: #7589AE;'>删除</a>";
			}
			
			$row['message'] = Better_Blog::parseBlogAt($row['message']);
			$time = Better_Functions::compareTime($row['dateline']);
			$return = "
			<div class='comments_list'>
				<div class='row' style='border: 0; font-size: 14px; padding-left: 0;width: 647px;'>
					<div class='avatar' style='padding-left: 0;'>
						<img style='padding: 2px;' src='{$row['user']['avatar_small']}' width='48'/>
					</div>
					<div class='msg' style='width: 585px; padding-right: 0;'>
						<p>
							<span class='username' style='font-weight: bold; font-family: arial;'><a href='/{$row['user']['username']}'>{$row['user']['nickname']}</a></span>&nbsp;{$row['message']}
							<br>
							<span style='color: #666; font-size: 12px;'>评论了{$type}：“<a href='/comments?bid={$bid}'>{$old_msg}</a>”</span>
						</p>
						<div style='font-size:12px;'>
							<span class='time left'>{$time}</span>
							<a href='javascript: void(0);' class='right' onclick='Better_Rtlist_Reply($(this));' style='margin-right: 15px; color: #7589AE;' bid='{$bid}' allow_rt='{$blog['allow_rt']}' nick='{$row['user']['nickname']}'>回复</a>
							{$del_btn}
							<div class='clearfix'></div>
						</div>
					</div>
				</div>
			</div>
			";
		
		return $return;
	
	}
	
}