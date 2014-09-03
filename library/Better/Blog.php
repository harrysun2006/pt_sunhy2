<?php

/**
 * 博客逻辑处理
 *
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Blog
{
	protected static $retry = array();
	protected static $atNames = array();
	protected static $atUids = array();
	
	/**
	 * 根据用户id获取他们所有的回复
	 *
	 * @param array $uids
	 * @param integer $page
	 * @param integer $pageSize
	 * @return array
	 */
	/*public static function getRepliesByUids($uids, $page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$uid = Better_Registry::get('sess')->getUid();
		
		$rows = Better_DAO_Blogreply::getInstance()->getBlogsByUids($uids);
		$data = array_chunk($rows, $pageSize);
		$return = array(
							'count' => count($rows),
							'rows' => isset($data[$page-1]) ? $data[$page-1] : array(),
							);
		unset($data);

		foreach ($return['rows'] as $k=>$row) {
			$return['rows'][$k] = self::parseBlogRow($row);
		}

		return $return;
	}*/
	
	/**
	 * 根据消息id取其评论
	 *
	 * @param string $bid
	 * @param integer $page
	 * @param integer $pageSize
	 * @return array
	 */
	public static function getRepliesByBid($bid, $page=1, $pageSize=20)
	{
		$result = array();
		
		if (self::validBid($bid)) {
			list($uid, $i) = explode('.', $bid);
			$rows = Better_DAO_Blogreply::getInstance($uid)->getRepliesByBid($bid, $page, $pageSize);
			
			foreach ($rows as $row) {
				if($row['uid']){
					$userInfo = Better_User::getInstance($row['uid'])->getUser();
					$row['user'] = $userInfo;
					$row['message'] = stripslashes($row['message']);
				}
				$result[] = $row;
			}
		}
		
		return $result;
	}
	
	
	/**
	 * 发表blog回复
	 */
	public static function  postReply($bid, $uid, $data){
		
		$id = 0;

		if ($uid) {
			$uid = (int)$uid;
			$user = Better_User::getInstance($uid);
			
			if ($user instanceof Better_User) {
				$userInfo = $user->getUser();
				
				if($data['to_shout']){
					$data['upbid'] = $bid;
					$data['real_upbid'] = $bid;
					
					$antiSpam = Better_Config::getAppConfig()->anti_spam->enable;
					$passbySpam = (isset($data['passby_spam']) && $data['passby_spam']) ? 1 : 0;
	
					if ($antiSpam && !$passbySpam && trim($data['message'])) {
						$offset = (int)Better_Config::getAppConfig()->anti_spam->offset;
						
						/**
						 * 2011-7-1[sunhy], 注意多进程(请求)同步问题:
						 * 参见http://10.35.254.100/mantisbt/view.php?id=1034
						 */
						$now = time();
						$cache = Better_Cache::remote();
						$key = 'spam_checktime_' . $uid;
						$r = $cache->add($key, $now, $offset);
						if ($r===false) $id = -5;

						$cacheMsg = $user->cache()->get('last_message');
						$lastMsg = $cacheMsg['message'];
						$lastPost = $cacheMsg['dateline'];
						if ($now-$lastPost < $offset) {
							$id = -5;
						} else if (trim($lastMsg) && trim($data['message']) && trim($lastMsg)==trim($data['message'])) {
							$id = -6;
						}
					}
					
					if($data['upbid']){
						$upblog = Better_User_Status::getInstance($uid)->byBid($bid);
						if($upblog['allow_rt']=='0'){
							$id = -7;
						}
			}
				}
				
				if($id!=-5 && $id!=-6 && $id!=-7){
					if ($userInfo['state']==Better_User_State::MUTE) {
						$id = -1;
					} /*else if ($userInfo['karma']<0) {
						$id = -4;
					}*/ else if ($userInfo['state']==Better_User_State::ENABLED 
						|| $userInfo['state']==Better_User_State::SIGNUP_VALIDATING 
						|| $userInfo['state']==Better_User_State::UPDATE_VALIDATING 
						|| $userInfo['state']==Better_User_State::LOCKED
						) {
	
						if ($data['message']) {
							$message = self::filterMessage($data['message']);
							if (Better_Filter::getInstance()->filterBanwords($message)) {
								$id = -2;
							}
						}
						
						if ($id!=-2) {
							if(self::validBid($bid)){
								
								list($blog_uid, $i) = explode('.', $bid);
								$insert_data = array(
									'blog_uid'=>$blog_uid,
									'bid'=>$bid,
									'uid'=>$uid,
									'message'=>self::filterMessage($data['message']),
									'dateline'=>time(),
									'checked'=>1,
									'source'=>$data['source'] ? $data['source'] : Better_Config::getAppConfig()->blog->default_source
								);
								$id = Better_DAO_Blogreply::getInstance($blog_uid)->insert($insert_data);
								list($up_uid, $up_i) = explode('.', $data['upbid']);
								$rtcounter = Better_DAO_User_Rtcounters::getInstance($up_uid)->get(array('bid' => $data['upbid']));
								//Better_Log::getInstance()->logInfo(serialize($rtcounter),'rtnums');
								$rtcounter['nums'] = $rtcounter['nums']+1;	
								$rtcounter['bid'] = $data['upbid'];							
								Better_DAO_User_Rtcounters::getInstance($up_uid)->replace($rtcounter);								
								$hooks = array();
								$hooks[] = 'Filter';
								if(!isset($data['notrme']) && $data['notrme']!=1){
									$hooks[] = 'Mentionme';
								}
								Better_Hook::factory($hooks)->invoke('ReplyPosted', array(
								'id' => $id,
								'data' => $data,
								'uid' => $uid,
								'blog_uid'=> $blog_uid,
								'message'=> $data['message'],
								'comment_data'=> $insert_data
								));
							}
							
							$lastResult = Better_Filter::getInstance()->getLastResult();
							
							if(is_array($lastResult) && in_array(3, $lastResult)){
								$id = -3; //审核发表
								//关闭审核发表的提示
								$id = 1;
							}
							
							if($data['to_shout']){
								self::postBlog($uid, $data);
							}
						}
						
					} else if ($userInfo['state']==Better_User_State::SIGNUP_VALIDATING || $userInfo['state']==Better_User_State::UPDATE_VALIDATING) {
						//	@TODO 用户帐号没有激活的情况
					}
			}
			
				if($id>0 || $id==-3){//评论成功清除blog cache
					$cacher = Better_Cache::remote();
					$cacheKey = md5('kai_blog_bid_'.$bid);	
					$cacher->set($cacheKey, null);
				}
			
			}
		}

		return $id;
		
	}
	
	
	
	/**
	 * 删除一个评论
	 *
	 * @param $id 消息id
	 */
	public static function deleteReply($id, $bid)
	{
		$flag = false;
		
		if($id && $bid){
			list($blog_uid, $i) = explode('.', $bid);
			
			$rtblog = Better_DAO_Blogreply::getInstance($blog_uid)->get(array('id'=>$id));
			
			
			$flag = Better_DAO_Blogreply::getInstance($blog_uid)->delete($id);
			
			if($flag){//删除成功清除blog cache
				$cacher = Better_Cache::remote();
				$cacheKey = md5('kai_blog_bid_'.$bid);	
				$cacher->set($cacheKey, null);
			}
			list($up_uid, $up_i) = explode('.', $rtblog['bid']);
		
			$rtcounter = Better_DAO_User_Rtcounters::getInstance($up_uid)->get(array('bid' => $rtblog['bid']));			
			$rtcounter['nums'] = $rtcounter['nums']-1;	
			$rtcounter['bid'] = $rtblog['bid'];							
			Better_DAO_User_Rtcounters::getInstance($up_uid)->replace($rtcounter);	
			
			$hooks = array('Filter', 'Mentionme');
			Better_Hook::factory($hooks)->invoke('ReplyDeleted', array(
				'id'=> $id,
				'bid'=> $bid
			));
		}
		
		
		return $flag;
	}
	
	/**
	 * 检查bid格式
	 *
	 * @param string $bid
	 * @return bool
	 */
	public static function validBid($bid)
	{
		return preg_match('/^([0-9]+).([0-9]+)$/is', $bid);
	}
	
	/**
	 * 增加博客收藏数量
	 *
	 * @param string $bid
	 * @param integer $add
	 * @return unknown_type
	 */
	public static function addFavorited($uid, $bid, $add=1)
	{
		return Better_DAO_Blog::getInstance($uid)->increase('favorited', array('bid' => $bid), $add);
	}
	
	/**
	 * 根据限制条件来查找Blog
	 * 
	 * @param $condition
	 * @param $bid Blog id in database
	 */
	public static function getBidByCond($uid,$poiid,$type)
	{
		return Better_DAO_Blog::getInstance($uid)->getBidByCond($uid,$poiid,$type);
	}
	
	/**
	 * 获取一个消息的详细数据
	 * （因为图片显示方式修改，该方法暂时没有用处）
	 *
	 * @param string $bid
	 * @return array
	 */
	public static function getBlog($bid)
	{
		list($uid, $cnt) = explode('.', $bid);
		$data = Better_DAO_Blog::getInstance($uid)->get($bid);
		$userInfo = array();

		if (isset($data['bid'])) {

			list($blog_uid, $o) = explode('.', $data['bid']);
			$data['comments'] = Better_DAO_Blogreply::getInstance($blog_uid)->getRepliesCount($data['bid']);

			$blogUserInfo = Better_User::getInstance($data['uid'])->getUser();

			$sessUid = Better_Registry::get('sess')->getUid();

			if (!in_array($data['uid'], Better_Registry::get('user')->blockedby)) {
				$data['message'] = self::dynFilterMessage($data['message']);
				
				$attach = $data['attach'] ? $data['attach'] : '';
				if ($attach) {
					$tmp = Better_Attachment::getInstance($attach)->parseAttachment();;
					$data['attach_tiny'] = $tmp['tiny'];
					$data['attach_url'] = $tmp['url'];
					$data['attach_thumb'] = $tmp['thumb'];
				} else {
					$data['attach_tiny'] = $data['attach_url'] = $data['attach_thumb'] = '';
				}
	
				$blogUserInfo['user_city'] = $blogUserInfo['city'];
				$blogUserInfo['user_address'] = $blogUserInfo['address'];
				$blogUserInfo['user_lon'] = $blogUserInfo['lon'];
				$blogUserInfo['user_lat'] = $blogUserInfo['lat'];
						
				if ($blogUserInfo['avatar']) {
					$data['avatar_thumb'] = Better_Registry::get('user')->getUserAvatar('thumb', $blogUserInfo);
					$data['avatar_url'] = Better_Registry::get('user')->getUserAvatar('normal', $blogUserInfo);
					$data['avatar_tiny'] = Better_Registry::get('user')->getUserAvatar('tiny', $blogUserInfo);
				} else {
					$data['avatar_tiny'] = $data['avatar_thumb'] = $data['avatar_url'] = Better_Config::getAttachConfig()->global->avatar->default_url;
				}

				if (!Better_Registry::get('sess')->get('admin_uid')) {
					if (($sessUid>0 && $sessUid!=$data['uid']) || !$sessUid) {
								list($data['lon'], $data['lat']) = Better_Functions::XY2LL($data['x'], $data['y']);
								list($data['user_lon'], $data['user_lat']) = Better_Functions::XY2LL($data['user_x'], $data['user_y']);
					}
				} else {
					list($data['lon'], $data['lat']) = Better_Functions::XY2LL($data['x'], $data['y']);
					list($data['user_lon'], $data['user_lat']) = Better_Functions::XY2LL($data['user_x'], $data['user_y']);
				}
				
				if ($data['poi_id']) {
					$data['blog_poi'] = Better_Poi_Info::getInstance(Better_Poi_Info::dehashId($data['poi_id']))->getBasic();
				}
							
				$data['location_tips'] = self::filterLocation($data);

				if ($data['badge_id']>0) {
					$badgeId = $data['badge_id'];
					$data['badge_id'] = 0;
					
					//$badges = Better_Cache::remote()->get('badge_table');
					$badge = Better_Badge::getBadge($badgeId);
					
					if ($badge) {
						$badgeParams = $badge->getParams();
						
						$data['badge_detail'] = array(
							'name' => $badgeParams['badge_name'],
							'picture' => $badgeParams['badge_picture']
							);
						$data['badge_id'] = $badgeId;
					}
				}
								
			}
		}
		
		return array(
					'blog' => $data,
					'user' => $blogUserInfo,
					);
	}
	
	/**
	 * 根据指定的uids读取消息，并渲染结果
	 *
	 * @param $uids
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public static function getByUids($uids, $page=1, $pageSize=BETTER_PAGE_SIZE, $passbyExcludes=false)
	{
		$sessUid = Better_Registry::get('sess')->get('uid');
		$sessUser = Better_User::getInstance($sessUid);
		
		$params = array(
			'page' => $page,
			'page_size' => $pageSize,
			'without_me' => true,
			'type' => 'normal',
			'uids' => $uids,
			);
		
		$return = $sessUser->blog()->getAllBlogs($params);
			
		$return['data'] = &$return['rows'];

		return $return;
	}
	
	/**
	 * 根据消息id数组获取所有消息
	 *
	 * @param array $bids
	 * @param integer $page
	 * @param integer $pageSize
	 * @return array
	 */
	public static function getByBids($bids, $page=1, $pageSize=BETTER_PAGE_SIZE, $mustHavePhoto=false, $reverse=true)
	{
		$uid = Better_Registry::get('sess')->getUid();
		$userInfo = $uid ? Better_Registry::get('user')->getUser() : array();
		
		$newBids = $bids;//self::filteBids($bids);
		
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			);

		if (count($newBids)>0) {

			$dao = Better_DAO_Blog::getInstance();
			$rows = $dao->getBlogsByBids($newBids, $userInfo);
			$count = count($rows);
			$data = array_chunk($rows, $pageSize);

			$return['count'] = $count;
			$return['rows'] = isset($data[$page-1]) ? $data[$page-1] : array();
			$upbids = array();
			unset($data);
		
			foreach ($return['rows'] as $k=>$row) {
				if ($row['upbid']) {
					$upbids[] = $row['upbid'];
				}
				$return['rows'][$k] = self::parseBlogRow($row);
			}

			$upbids = array_unique($upbids);
			if (count($upbids)>0 && $reverse==true) {
				$uprows = self::getByBids($bids, 1, $pageSize, $mustHavePhoto, false);
				foreach ($uprows['rows'] as $row) {
					$return['rts'][$row['bid']] = self::parseBlogRow($row);
				}
			}
		} else {
			$return = array(
				'count' => 0,
				'rows' => array(),
				);
		}


		return $return;
	}

	/**
	 * 读取最新的消息，并渲染结果
	 *
	 * @param $page
	 * @param $pageSize
	 * @return array
	 */
	public static function getLastest($page=1,$pageSize=BETTER_PAGE_SIZE, $userInfo=array())
	{
		if (!isset($userInfo['uid'])) {
			$uid = Better_Registry::get('sess')->getUid();
			$userInfo = $uid ? Better_Registry::get('user')->getUser() : array();
		} else {
			$uid = $userInfo['uid'];
		}
		
		$rows = Better_DAO_Blog::getInstance()->getLastest(Better_Registry::get('user')->blocks, $userInfo, $page, $pageSize);
		$data = array_chunk($rows, $pageSize);
		$return = array(
							'count' => count($rows),
							'rows' => $data[ $page -1 ],
							'rts' => array(),
							);
		unset($data);
		$uid = Better_Registry::get('sess')->getUid();

		$upbids = array();
		foreach ($return['rows'] as $k=>$row) {
			if ($row['upbids']) {
				$upbids[] = $row['upbids'];
			}
			$return['rows'][$k] = self::parseBlogRow($row);
		}
		
		if (count($upbids)>0) {
			$upbids = array_unique($upbids);
			$uprows = self::getByBids($upbids, 1, $pageSize, false, false);
			foreach ($uprows['rows'] as $row) {
				$return['rts'][$row['bid']] = self::parseBlogRow($row);
			}
		}
		
		return $return;
	}
	
	public static function getIndex($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$rows = Better_DAO_Blog::getInstance()->getIndex();
		$data = array_chunk($rows, $pageSize);
		$return = array(
							'count' => count($rows),
							'rows' => $data[ $page -1 ],
							'rts' => array(),
							);
							
		foreach ($return['rows'] as $k=>$row) {
			$return['rows'][$k] = self::parseBlogRow($row);
		}							
							
		unset($data);
		
		return $return;		
	}
	
	/**
	 * 将某个微博设为需要审核
	 * 
	 * @param $bid
	 * @return unknown_type
	 */
	public static function setNeedCheck($bid, $needCheck=true)
	{
		list($uid, $cnt) = explode('.', $bid);
		Better_DAO_Blog::getInstance($uid)->updateByCond(array(
			'checked' => $needCheck==true ? '0' : '1',
			'synced' => $needCheck==true ? '0' : '1',
			), array(
			'bid' => $bid
			));
	}

	/**
	 *	发布一个新消息
	 *
	 * @param $uid 用户id
	 * @param $data 要写入数据库的数组
	 * @return integer
	 */
	public static function post($uid, $data, $offset = '')
	{
		$id = 0;

		if ($uid) {
			$upbid = $data['upbid'];
			$real_upbid = $data['real_upbid'] = isset($data['real_upbid'])? $data['real_upbid'] : $upbid;
			
			//判断是否允许转发
			if($real_upbid && $real_upbid!=$upbid){
				$rupblog = Better_User_Status::getInstance($uid)->byBid($real_upbid);
				
				if($rupblog['upbid']!= $upbid){
					$data['real_upbid'] = $upbid;
				}else{
					if($rupblog['allow_rt']=='0'){
						$id = -7;
						return $id;
					}
				}
			}
			if($upbid){
				$upblog = Better_User_Status::getInstance($uid)->byBid($upbid);
				
				if($upblog['allow_rt']=='0'){
					$id = -7;
					return $id;
				}
			}
			
			$uid = (int)$uid;
			$user = Better_User::getInstance($uid);

			if ($user instanceof Better_User) {
				$userInfo = $user->getUser();

				$antiSpam = Better_Config::getAppConfig()->anti_spam->enable;
				$passbySpam = (isset($data['passby_spam']) && $data['passby_spam']) ? 1 : 0;

				if ($antiSpam && !$passbySpam && ($upbid || $data['attach'] || trim($data['message']))) {
					//Add 2011-04-28 同步博客防止间隔太短 zhoul@peptalk.cn
					$offset = intval($offset);
					if ($offset <= 0) {
						$offset = (int)Better_Config::getAppConfig()->anti_spam->offset;
					}

					/**
					 * 2011-7-1[sunhy], 注意多进程(请求)同步问题:
					 * 参见http://10.35.254.100/mantisbt/view.php?id=1034
					 * 2011-8-18[sunhy]: memcache貌似有bug, 有时不会过期, 需要双重检查!!!
					 */
					$now = time();
					$cache = Better_Cache::remote();
					$key = 'spam_checktime_' . $uid;
					$r = $cache->add($key, $now, $offset);
					if ($r===false) { // 尚未过期
						$lastTime = intval($cache->get($key));
						if ($lastTime > 0 && $now - $lastTime < $offset) {
							$id = -5;
						} else { // memcache bug, 清掉已过期memcache应该清掉的缓存
							$cache->remove($key);
							$cache->add($key, $now, $offset);
						}
					}

					$cacheMsg = $user->cache()->get('last_message');
					$lastMsg = $cacheMsg['message'];
					$lastPost = $cacheMsg['dateline'];
					$lastAttmd5 = $cacheMsg['attmd5'];
					$lastRealUpbid = $cacheMsg['real_upbid'];
					if ($data['attach']) {
						$attachInfo = Better_Attachment_Parse::parse($data['attach']);
						// $data['attmd5'] = md5_file($attachInfo['tiny_save_path']);
						// ImageMagick对PNG图片的缩放处理每次生成的文件不一样!!!只能使用原文件的md5!!!
						$data['attmd5'] = $attachInfo['hash'];
					} else {
						$data['attmd5'] = '';
					}
					if ($now-$lastPost < $offset) {
						$id = -5;
					} else if (trim($lastMsg)==trim($data['message']) && $lastAttmd5==$data['attmd5'] && $lastRealUpbid==$real_upbid) {
						$id = -6;
					}
				}
				
				if ($id!=-5 && $id!=-6) {
					if ($userInfo['state']==Better_User_State::MUTE) {
						$id = -2;
					} /*else if ($userInfo['karma']<0) {
						$id = -4;
					}*/ else if ($userInfo['state']==Better_User_State::ENABLED 
						|| $userInfo['state']==Better_User_State::SIGNUP_VALIDATING 
						|| $userInfo['state']==Better_User_State::UPDATE_VALIDATING 
						|| $userInfo['state']==Better_User_State::LOCKED
						) {
	
						if ($data['message']) {
							$message = self::filterMessage($data['message']);
							if (Better_Filter::getInstance()->filterBanwords($message)) {
								$id = -3;
							}
						}
						
						if (($data['type']=='todo' || $data['type']=='checkin') && $id==-3) {
							$data['message'] = '';
						} 
						if($data['type']=='checkin' && $bid = Better_DAO_Todo::getInstance($uid)->getBidByPoi($uid,$data['poi_id'])){
							//如果该ＰＯＩ为我想去的ＰＯＩ，需要先执行我已去过的操作
								self::beentodo($bid);
						}
						
						if ($id!=-3 || $data['type']=='checkin' || $data['type']=='todo') {
							$id = self::postBlog($uid, $data);
							$lastResult = Better_Filter::getInstance()->getLastResult();
							
							if(is_array($lastResult) && in_array(3, $lastResult)){
								$id = -1; //审核发表
								//审核发表的信息部丢给用户
								$id = 1;
							}
						}
						
					} else if ($userInfo['state']==Better_User_State::SIGNUP_VALIDATING || $userInfo['state']==Better_User_State::UPDATE_VALIDATING) {
						//	@TODO 用户帐号没有激活的情况
					}
				}
			}
		}

		return $id;
	}

	/**
	 * 发表消息回复
	 *
	 * @TODO 当指定了一个不存在的用户时的处理
	 * @param $uid
	 * @param $data
	 * @return unknown_type
	 */
	/*protected static function postBlogReply($uid, $data)
	{
		$user = Better_User::getInstance($uid);
		$info = $user->getUser();
		$id = 0;

		$username = preg_replace('/^@([0-9a-zA-Z\-_]+)([\s\n]+)(.+)$/is', '\1', $data['message']);
		$ruserInfo = Better_user::getInstance()->getUserByUsername($username);
		$ruid = 0;
		
		if (isset($ruserInfo['uid']) && $ruserInfo['uid']) {
			$ruid = $ruserInfo['uid'];
		} else if (isset($data['upbid'])) {
			list($ruid, $rposts) = explode('.', $data['upbid']);
			$ruserInfo = Better_User::getInstance($ruid)->getUser();
		}
		
		$s = array();
		$s['bid'] = '';

		if ($ruid) {
			
			$s['bid'] = $info['uid'].'.'.$info['posts'];
			$s['message'] = self::filterMessage($data['message']);
			$s['type'] = isset($data['type']) ? $data['type'] : 'normal';
			$s['poi_id'] = $data['poi_id'];
			$s['upbid'] = $data['upbid'] ? $data['upbid'] : 0;
			$s['dateline'] = time();
			$s['uid'] = $info['uid'];
			$s['ruid'] = $ruid;
			$s['ip'] = Better_Functions::getIP();
			$s['attach'] = $data['attach'];
			$s['source'] = $data['source'] ? $data['source'] : Better_Config::getAppConfig()->blog->default_source;
			$s['x'] = isset($data['x']) ? $data['x'] : $info['x'];
			$s['y'] = isset($data['y']) ? $data['y'] : $info['y'];
			if (isset($data['lon']) && isset($data['lat'])) {
				list($s['x'], $s['y']) = Better_Functions::LL2XY($data['lon'], $data['lat']);
			} else {
				list($data['lon'], $data['lat']) = Better_Functions::XY2LL($s['x'], $s['y']);
			}			
			$s['city'] = isset($data['city']) ? $data['city'] : $info['city'];
			$s['address'] = isset($data['address']) ? $data['address'] : $info['address'];
			$s['range'] = isset($data['range']) ? $data['range'] : $info['range'];
			$s['realbid'] = isset($data['realbid']) ? $data['realbid'] : 0;
			
			if (isset($data['lon']) && isset($data['lat'])) {
				list($s['x'], $s['y']) = Better_Functions::LL2XY($data['lon'], $data['lat']);
			}

			$id = Better_DAO_Blogreply::getInstance($ruid)->insert($s);
			
			if ($id) {
				Better_Hook::factory(array(
					'User', 'Filter', 'Notify'
				))->invoke('BlogReplyPosted', array(
					'uid' => $info['uid'],
					'blog' => $s,
					'ruid' => $ruid
				));
			}
		} else {
			Better_Log::getInstance()->logAlert("REPLY_FAILED_".$data['uid']);
		}
		
		return $s['bid'];
	}*/
	
	
	protected static function postMafAttach($uid)
	{
		$img = Better_Config::getAppConfig()->maf_card_img;		
		$imgThumb = Better_Config::getAppConfig()->maf_card_img_thumb;		
		$imgTiny = Better_Config::getAppConfig()->maf_card_img_tiny;		
		$servers = (int) Better_Config::getAttachConfig()->global->servers;
	
		$seq = Better_Attachment_Save::getSequnce();
		$file_id = $uid.'.'.$seq;		
		$basePath = '';
	
		$basePath = Better_Config::getAttachConfig()->{'attach_server_'.$servers}->save_path.$basePath;
		$savePath = Better_Attachment_Save::hashDir($seq, $basePath, true);//路径建好了？
		
		
		copy($img, $savePath.'/'.$file_id.'.jpg');
		copy($imgThumb, $savePath.'/thumb_'.$file_id.'.jpg');
		copy($imgTiny, $savePath.'/tiny_'.$file_id.'.jpg');
		
		$d = array();
		$d['dateline'] = time();
		$d['filename'] = Better_Config::getAppConfig()->maf_card_img_name;
		$d['filesize'] = 48282;
		$d['ext'] = 'jpg';
		$d['uid'] = $uid;
		$d['file_id'] = $file_id;
		
		Better_DAO_Attachment::getInstance($uid)->insert($d);
		
		Better_DAO_AttachAssign::getInstance()->insert(array(
			'fid' => $d['file_id'],
			'sid' => $servers,
			));	
		return $file_id;
	}

	/**
	 * 发表消息
	 *
	 * @param integer $uid
	 * @param array $data
	 * @return unknown_type
	 */
	protected static function postBlog($uid, $data)
	{
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		$nbid = 0;

		$need_sync = isset($data['need_sync']) ? $data['need_sync'] : 1;
		
		if (isset($data['is_maf']) && $data['is_maf']) {
			$mafAttach = self::postMafAttach($uid);			
			$data['attach'] = $mafAttach;
		}
		
		$s = array();
		$s1=array();
		$s['bid'] = $userInfo['uid'].'.'.$userInfo['posts'];
		$s1['bid'] = $userInfo['uid'].'.'.$userInfo['posts'];
		$s['message'] = self::filterMessage($data['message']);
		$s1['message'] = self::filterMessage($data['message']);
		$s['upbid'] = $data['upbid'] ? $data['upbid'] : 0;
		$s['real_upbid'] = $data['real_upbid']? $data['real_upbid']: 0;
		$s['dateline'] = $data['dateline'] ? $data['dateline'] : time();
		$s1['dateline'] = time();
		$s['uid'] = $userInfo['uid'];
		$s1['uid'] = $userInfo['uid'];
		$s['ip'] = isset($data['ip']) ? $data['ip'] : Better_Functions::getIP();
		$s['attach'] = $data['attach'];
		$s['source'] = isset($data['source']) ? $data['source'] : Better_Config::getAppConfig()->blog->default_source;
		$s['synced'] = 1;		
		$s['type'] = isset($data['type']) ? $data['type'] : 'normal';
		$s['need_sync'] = $need_sync;
		
		$nokarma = isset($data['nokarma']) ? $data['nokarma'] : 0;
		$passbyFilter = (isset($data['passby_filter']) && $data['passby_filter']) ? true : false;
		
		switch ($data['priv']) {
			case 'friend':
			case 'protected':
				//$s['priv'] = 'protected';
				$s['priv'] = 'public';
				break;
			case 'private':
				$s['priv'] = 'private';
				break;
			default:
				$s['priv'] = 'public';
				break;
		}
		$s['major'] = isset($data['major']) ? $data['major'] : 0;
		
		if ($data['poi_id']) {
			$poiInfo = Better_Poi_Info::getInstance($data['poi_id'])->getBasic();
			
			$s['x'] = isset($poiInfo['x']) ? $poiInfo['x'] : $userInfo['x'];
			$s['y'] = isset($poiInfo['y']) ? $poiInfo['y'] : $userInfo['y'];

			$s['city'] = isset($poiInfo['city']) ? $poiInfo['city'] : $userInfo['city'];
			$s['address'] = isset($poiInfo['address']) ? $poiInfo['address'] : $userInfo['address'];			
			$s['poi_id'] = $data['poi_id'];
			$s1['poi_id'] = $data['poi_id'];
		} else if (isset($data['lon']) && isset($data['lat'])) {
			list($s['x'], $s['y']) = Better_Functions::LL2XY($data['lon'], $data['lat']);
		} else if (isset($data['x']) && isset($data['y'])) {
			list($data['lon'], $data['lat']) = Better_Functions::XY2LL($s['x'], $s['y']);
		} else {
			$s['x'] = 0;
			$s['y'] = 0;

			$s['city'] = '';
			$s['address'] = '';			
			$s['poi_id'] = 0;				
		}

		$s['range'] = isset($data['range']) ? $data['range'] : $userInfo['range'];
		$s['badge_id'] = isset($data['badge_id']) ? $data['badge_id'] : 0;
		
		if($s['priv'] == 'public' && Better_Config::getAppConfig()->market->wlan->switch && $s['attach']){						
			$checked = '/cmcc/';
			$tempmessage1 = Better_Filter::make_semiangle($s['message']);
			$tempmessage2 = strtolower($tempmessage1); 								
			if (preg_match($checked, $tempmessage2)) 
			{				
				list($lon,$lat) = Better_Functions::XY2LL($s['x'], $s['y']);			
				$realattach['thumb'] = '';
				if($s['attach']){
					if(preg_match('/^([0-9]+).([0-9]+)$/', $s['attach']))	{
						$realattach = Better_Attachment_Parse::getInstance($s['attach'])->result();						
					} else if (preg_match('/^http(.+)$/', $s['attach'])) {
						$realattach['attach_tiny'] = $realattach['attach_thumb'] = $realattach['attach_url'] = $s['attach'];
					}
				}				
				$wlandate = array(
					'nickname' =>$userInfo['nickname'],
					'img' =>$userInfo['avatar_url'],
					'content' =>$s['message'],
					'photo' =>$realattach['thumb'],
					'lon' =>$lon,
					'lat' =>$lat,
					'posttm' =>time(),
					'checktm' =>0,
					'synctm' =>0,					
					'status' =>0
				);
				
				Better_DAO_Wlanblog::getInstance()->insert($wlandate);
			}			
		}
		/*
		Better_Log::getInstance()->logInfo(Better_Config::getAppConfig()->market->yongle->switch."--".$data['poi_id']."--".Better_Config::getAppConfig()->market->yongle->poilist,'yongle');
		if(Better_Config::getAppConfig()->market->yongle->switch && $data['poi_id']==Better_Config::getAppConfig()->market->yongle->poilist){
			$checked = '/YL+/';											
			if (preg_match($checked, $s['message'])){	
				Better_Log::getInstance()->logInfo("Had",'yongle');			
				$tempendstr = Better_Market_Yongle::getInstance()->checkid($s['message']);
				$s['message'] = $tempendstr;
			}			
		}
		*/
		

		try {
			
			$nbid = Better_DAO_Blog::getInstance($userInfo['uid'])->insert($s);
		
			if($s['priv'] != 'private' && Better_Config::getAppConfig()->rtautotore->switch && $s['upbid']>0 && $s['message']!='转发' && $data['to_shout']!=1 && $s['message']!=''){
				$s['notrme']=1;
				self::postReply($s['upbid'], $s['uid'], $s);
			}
			
			if($s['type'] == 'todo'){ //地点邀请时许额外添加记录
				 Better_DAO_Todo::getInstance($userInfo['uid'])->insert($s1);
			}
			if ($nbid ) {
				Better_Registry::set('blog_last_params', $s);

				$hooks = array();
				if (!$passbyFilter) {
					$hooks[] = 'Filter';
				}
				
				$hooks[] = 'Poi';
				$hooks[] = 'User';
				$hooks[] = 'Notify';
				$hooks[] = 'Karma';
				$hooks[] = 'Badge';
				$hooks[] = 'Ping';
				$hooks[] = 'Syncsites';
				$hooks[] = 'Cache';
				$hooks[] = 'Clean';		
				$hooks[] = 'Queue';
				$hooks[] = 'Publictimeline';
			
				if(!$nokarma){
					$hooks[]= 'Rp';
				}	
				
				if($s['priv']!='private'){
					$to_shout = isset($data['to_shout']) ? $data['to_shout'] : 0;
					!$to_shout && $hooks[] = 'Mentionme';//提到我的hook
				}
				//获得勋章后不防刷，不写入Better_Hook_Cache cache
				$badge_tm = 1;
				if($s['badge_id']>0){
					$badge_tm =0;
				}
				Better_Hook::factory($hooks)->invoke('BlogPosted', array(
					'blog' => $s,
					'data' => $data,
					'bid' => $nbid,				
					'uid' => $uid,
					'source' => isset($s['source']) ? $s['source'] : 'kai',
					'need_sync'=> $need_sync,
					'badge_tm' => $badge_tm
					));
			} else {
				if (!defined('BETTER_IN_FREIND_REQUEST_BLOG')) {
					self::$retry[$uid] = self::$retry[$uid] ? self::$retry[$uid]+1 : 1;
					
					if (self::$retry[$uid]<=3) {
						$user->updateUser(array(
							'posts' => $userInfo['posts']+1
							));
						self::postBlog($uid, $data);
					}
				}
			}

		} catch (Exception $e) {
			Better_Log::getInstance()->logAlert('POST_NEW_BLOG_FAILED:['.$e->getMessage().']', 'blog');
			$user->updateUser(array(
				'posts' => $userInfo['posts']+1
				));
			$nbid && self::delete($nbid);
			$nbid = 0;
		}
		
		/**
		 * 如果微博包含敏感字符
		 * 
		 * @TODO 
		 * 1、增加相应api出错提示
		 * 2、修改AjaxController
		 * 3、修改发微博的相关js: home.js、place.js、share.js等等
		 * 
		if (Better_Filter::getInstance()->getLastResult()) {
			$nbid = -1;
		}**/

		return $nbid;
	}
	
	/**
	 * 不想去了
	 * @param $bid
	 */
	public static function canceltodo($bid)
	{
		list($uid, $cnt) = explode('.', $bid);
		$todo = Better_DAO_Todo::getInstance($uid)->get($bid);
		$flag = false;
		if (isset($todo['bid'])) {
			$bids = Better_DAO_Todo::getInstance($todo['uid'])->getBids($todo['uid'],$todo['poi_id']);
			
			foreach($bids as $b){
				$flag = Better_DAO_Todo::getInstance($uid)->delete($b['bid']);
						//add to log
				$logarr=array();
				$logarr['bid'] =$b['bid']; 
				$logarr['uid'] = $todo['uid'];
				$logarr['poi_id'] = $todo['poi_id'];
				$logarr['message'] =$todo['message']; 
				$logarr['type']=0;
				$logarr['dateline']=time();
				$flag = Better_DAO_TodoLog::getInstance($uid)->insert($logarr);					
			}
		}	
		if($flag){					
			$msg = '用户【'.$todo['uid'].'】取消了地点【'.$todo['poi_id'].'】想去计划。';
			Better_Log::getInstance()->log($msg,'user_poi_todo');
			self::delete($bid);
		}	
		return $flag;
	}
	/**
	 * 已经去过了
	 * @param unknown_type $bid
	 */
	public static function beentodo($bid)
	{
		list($uid, $cnt) = explode('.', $bid);
		$todo = Better_DAO_Todo::getInstance($uid)->get($bid);
		$flag = false;
		if (isset($todo['bid'])) {
			$bids = Better_DAO_Todo::getInstance($todo['uid'])->getBids($todo['uid'],$todo['poi_id']);
			foreach($bids as $b){
				$flag = Better_DAO_Todo::getInstance($uid)->delete($b['bid']);
						//add to log
				$logarr=array();
				$logarr['bid'] = $b['bid']; 
				$logarr['uid'] = $todo['uid'];
				$logarr['poi_id'] = $todo['poi_id'];
				$logarr['message'] =$todo['message']; 
				$logarr['type']=1;
				$logarr['dateline']=time();
				$flag = Better_DAO_TodoLog::getInstance($uid)->insert($logarr);					
			}
		}	
		if($flag){					
			$msg = '用户【'.$todo['uid'].'】取消了地点【'.$todo['poi_id'].'】想去计划。';
			Better_Log::getInstance()->log($msg,'user_poi_todo');
		}
		return $flag;
	}
	/**
	 * 删除一个消息
	 *
	 * @param $bid 消息id
	 */
	public static function delete($bid)
	{
		list($uid, $cnt) = explode('.', $bid);
		$bdata = self::getBlog($bid);
		$flag = false;

		if (isset($bdata['blog']['bid'])) {
			$blog = &$bdata['blog'];
			$userInfo = &$bdata['user'];
			
			$flag = Better_DAO_Blog::getInstance($uid)->delete($bid);
			
			if ($bdata['blog']['attach']) {
				$delete = Better_Attachment::getInstance($bdata['blog']['attach']);
				$att = $delete->parseAttachment();
				$filename = $att['filename'];
				
				if ($delete && $delete->delete()) { 
					$delete->delete()->delete();
				}
			}
			$hook = array(
				'Karma', 'Poi', 'User', 'Newblog', 'Debug', 'BlogReply', 'DirectMessage', 'Admin_DirectMessage', 'Public', 'Publictimeline', 'Filter', 'Syncsites', 'Cache', 'Queue', 'Publictimeline', 'Mentionme'
				);
			$adminuid = Better_Registry::get('sess')->get('admin_uid');
			if($adminuid>0){
				$hook[] = 'Rp';
			}
			Better_Log::getInstance()->logInfo(serialize($hook),'xxx');
			Better_Hook::factory($hook)->invoke('BlogDeleted', array(
				'blog' => &$bdata['blog'],
				'userInfo' => $userInfo,
				'filename'=> $filename,
				'co_uid'=> $adminuid
				));
		}
		
		return $flag;
	}

	/**
	 *	过滤消息文字（显示给用户前）
	 * 与filterMessage不同的是，这个方法要处理一些语言包相关的字符串
	 *
	 * @param $txt 消息文字
	 * @return string
	 */
	public static function dynFilterMessage($txt)
	{
		$txt = stripslashes($txt);
		return $txt;
	}

	/**
	 *	过滤消息文字（写入数据库前）
	 * 需要进行如下操作：
	 * 1、解析http://开头的链接
	 * 2、解析@+用户名开头的文字
	 * 3、解析转发消息 RT+用户名
	 *
	 * @param $txt 消息文字
	 * @return string
	 */
	protected static function filterMessage($txt)
	{
		//$txt = strip_tags($txt);
		$txt = trim($txt);
		$txt = iconv_substr($txt, 0, 160, 'utf-8');
		$txt = htmlspecialchars(addslashes($txt));
		
		//	短地址格式化
		if (Better_Config::getAppConfig()->short_url) {
			$pat = '/((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w]+\.([\.a-zA-Z0-9]+)((\?|\/|:)+[\w\.\/=\?%\-&~`@\':+!#]*)*)/ies';
			$txt = preg_replace($pat , "Better_Url::toDb('\\1')", $txt);
		}

		return $txt;
	}
	
	/**
	 * 反向解析格式化后的消息，操作过程与filterMessage相反
	 *
	 * @param $txt
	 * @return unknown_type
	 */
	public static function reverseMessage($txt)
	{
		return $txt;
	}
	
	/**
	 * 反向解析用户链接
	 *
	 * @param array $mats
	 * @return string
	 */
	protected static function reverseUser($mats)
	{
		$url = '@'.$mats[2];
		return $url;
	}
	
	/**
	 * 反向解析http链接
	 *
	 * @param array $mats
	 * @return string
	 */
	protected static function reverseHttp($mats)
	{
		$url = $mats[2];
		return $url;
	}
	
	/**
	 * 解析@+用户名
	 *
	 * @param array $mats
	 * @return string
	 */
	protected static function replaceAt($mats)
	{
		$username = $mats[2];
		$url = '<a href="/'.$username.'" class="blank">@'.$username.'</a>'.$mats[3];
		
		return $url;
	}

	/**
	 * 解析RT+转发
	 *
	 * @param array $mats
	 * @return string
	 */
	protected static function replaceRt($mats)
	{
		$str = '';
		$str .= Better_Registry::get('lang')->blog->rt.' ';
		$str .= '<a href="/'.$mats[1].'" class="blank">@'.$mats[1].'</a>'.$mats[2].$mats[3];

		return $str;
	}
	
	/**
	 * 解析回复
	 *
	 * @param array $mats
	 * @return string
	 */
	protected static function replaceRp($mats)
	{
		$str = Better_Registry::get('lang')->blog->reply.' '.$mats[0];
		return $str;
	}

	protected static function cleanUrl($mats)
	{
		$url	=	''	;
		$url	=	$mats[1].' <a href="'.strip_tags($mats[2]).'" class="blank">'.strip_tags($mats[2]).'</a>'	 ;
		$url	=	str_replace('&quot;','',$url)	;
		$url	=	str_replace('&quot','',$url)	;
		$url	=	trim($url,';');

		return $url	;
	}
	
	/**
	 * 解析从数据库中取出的消息数据
	 *
	 * @param array $row
	 * @return array
	 */
	public static function parseBlogRow(&$row)
	{
		$uid = Better_Registry::get('sess')->getUid();

		if ($row['attach'] && !$row['file_id'] && trim($row['attach']) && strlen($row['attach'])>=5) {
			$attach = Better_Attachment_Parse::getInstance($row['attach'])->result();

			$row['attach_tiny'] = $attach['tiny'];
			$row['attach_thumb'] = $attach['thumb'];
			$row['attach_url'] = $attach['url'];
		} else if ($row['attach'] && $row['file_id']) {
			$attach = Better_Attachment_Parse::getInstance($row)->result();

			$row['attach_tiny'] = $attach['tiny'];
			$row['attach_thumb'] = $attach['thumb'];
			$row['attach_url'] = $attach['url'];
		} else if ($row['attach'] && $row['file_id']) {
		} else {
			$row['attach_url'] = $row['attach_tiny'] = $row['attach_thumb'] = '';
		}

		if ($row['avatar']) {
			$tmp = $row;
			unset($tmp['file_id']);
			
			$row['avatar_thumb'] = Better_Registry::get('user')->getUserAvatar('thumb', $tmp);
			$row['avatar_normal'] = $row['avatar_url'] = Better_Registry::get('user')->getUserAvatar('normal', $tmp);
			$row['avatar_tiny'] = Better_Registry::get('user')->getUserAvatar('tiny', $tmp);
		} else {
			$row['avatar_normal'] = $row['avatar_tiny'] = $row['avatar_thumb'] = $row['avatar_url'] = Better_Attachment::getInstance()->getConfig()->global->avatar->default_url;
		}

		
					list($row['lon'], $row['lat']) = Better_Functions::XY2LL($row['x'], $row['y']);
					list($row['user_lon'], $row['user_lat']) = Better_Functions::XY2LL($row['user_x'], $row['user_y']);
		

		if ($row['poi_id']) {
			$row['poi'] = & Better_Poi_Info::getInstance($row['poi_id'])->getBasic();
			if($row['poi'] && $row['poi']['closed']){
				$row['city'] = '';
			}
		} else if ($row['last_checkin_poi']) {
			$row['user_poi'] = & Better_Poi_Info::getInstance($row['last_checkin_poi'])->getBasic();
		} else {
			$row['poi'] = array();
		}
		
		$row['location_tips'] = self::filterLocation($row);
		$row['message'] = self::dynFilterMessage($row['message']);
		($row['x'] && $row['y']) && list($row['lon'], $row['lat']) = Better_Functions::XY2LL($row['x'], $row['y']);
		($row['user_x'] && $row['user_y']) && list($row['user_lon'], $row['user_lat']) = Better_Functions::XY2LL($row['user_x'], $row['user_y']);

		if ($row['badge_id']>0) {
			$badgeId = $row['badge_id'];
			$row['badge_id'] = 0;
			
			//$badges = Better_Cache::remote()->get('badge_table');
			$badge = Better_Badge::getBadge($badgeId);
			
			if ($badge) {
				$badgeParams = $badge->getParams();
				
				$row['badge_detail'] = array(
					'name' => $badgeParams['badge_name'],
					'picture' => $badgeParams['badge_picture'],
					'big_picture'=>$badgeParams['badge_big_picture'],
					'got_tip'=>$badgeParams['got_tips'],
					'total' => $badgeParams['total']
					);
				$row['badge_id'] = $badgeId;
			}
		}
		
		$polled = 1;
		if ($row['type']=='tips' && $uid) {
			$polled = Better_Poi_Poll::getInstance($row['bid'])->isPolled($uid) ? 1 : 0;
		}
		
		$row['polled'] = (int)$polled;
		if(!isset($row['comments'])){
			list($blog_uid, $o) = explode('.', $row['bid']);
			//Better_Log::getInstance()->logInfo($row['bid'],'rtcomments');
			$row['comments'] = Better_DAO_Blogreply::getInstance($blog_uid)->getRepliesCount($row['bid']);
		}
		//2011-2-10 yangl
		$row['priv_blog'] = 1;
		$row['followers'] = 0;
		$row['followings'] = 0;
		
		return $row;
	}
	
	/**
	 * 根据用户隐私设置过滤位置的显示
	 *
	 * @param array $data
	 * @return string
	 */
	public static function filterLocation($data=array())
	{
		$location = '';
		if (count($data)==0) {
			$data = Better_Registry::get('user')->getUser();
		}
		
		if ($data['poi_id']==0) {
			
		} else {
		
			$lang = Better_Registry::get('lang');
				
			$city = ($data['user_city']=='' || $data['city']!=$data['user_city']) ? $data['city'] : $data['user_city'];
			$address = ($data['user_address']=='' || $data['address']!=$data['user_address']) ? $data['address'] : $data['user_address'];
			$address = $data['poi'] ? $data['poi']['name'] : '';
			
			if ($data['uid']) {
				$location = $city.' '.$address;
			} else {
				$location = '';
			}
	
			$location = trim($location);
		}

		return $location;
	}
	
	/**
	 * 根据bids读围脖时，先根据一些策略过滤一下
	 * 
	 * @param $bids
	 * @return array
	 */
	public static function &filteBids(&$bids)
	{
		$newBids = array();
		$uid = Better_Registry::get('sess')->getUid();
		
		if ($uid) {
			$userInfo = Better_User::getInstance($uid)->getUser();
			$blocked_uids = &Better_User::getInstance($uid)->blocks;
			if (count($blocked_uids)) {
				foreach ($bids as $bid) {
					list($_uid, $_posts) = explode('.', $bid);
					if ($_uid==$uid || !in_array($_uid, $blocked_uids)) {
						$newBids[] = $bid;
					}
				}
			} else {
				$newBids = $bids;
			}
		} else {
			$newBids = $bids;
		}
		unset($bids);
			
		return $newBids;
	}
	
	public static function parseWebBlogRow(&$row)
	{
		$uid = Better_Registry::get('sess')->getUid();

		if ($row['attach'] && !$row['file_id'] && trim($row['attach']) && strlen($row['attach'])>=5) {
			$attach = Better_Attachment_Parse::getInstance($row['attach'])->result();

			$row['attach_tiny'] = $attach['tiny'];
			$row['attach_thumb'] = $attach['thumb'];
			$row['attach_url'] = $attach['url'];
			$haveattache =1;
		} else if ($row['attach'] && $row['file_id']) {
			$attach = Better_Attachment_Parse::getInstance($row)->result();

			$row['attach_tiny'] = $attach['tiny'];
			$row['attach_thumb'] = $attach['thumb'];
			$row['attach_url'] = $attach['url'];
			
			$haveattache =1;
		} else if ($row['attach'] && $row['file_id']) {
		} else {
			$row['attach_url'] = $row['attach_tiny'] = $row['attach_thumb'] = '';
		}

		if ($row['avatar']) {
			$tmp = $row;
			unset($tmp['file_id']);
			
			$row['avatar_thumb'] = Better_Registry::get('user')->getUserAvatar('thumb', $tmp);
			$row['avatar_normal'] = $row['avatar_url'] = Better_Registry::get('user')->getUserAvatar('normal', $tmp);
			$row['avatar_tiny'] = Better_Registry::get('user')->getUserAvatar('tiny', $tmp);
		} else {
			$row['avatar_normal'] = $row['avatar_tiny'] = $row['avatar_thumb'] = $row['avatar_url'] = Better_Attachment::getInstance()->getConfig()->global->avatar->default_url;
		}

		if ($uid && $uid==$row['uid']) {
			list($row['lon'], $row['lat']) = Better_Functions::XY2LL($row['x'], $row['y']);
			list($row['user_lon'], $row['user_lat']) = Better_Functions::XY2LL($row['user_x'], $row['user_y']);
		} else if (($uid>0 && $uid!=$row['uid']) || !$uid) {
					list($row['lon'], $row['lat']) = Better_Functions::XY2LL($row['x'], $row['y']);
					list($row['user_lon'], $row['user_lat']) = Better_Functions::XY2LL($row['user_x'], $row['user_y']);
		}

		if ($row['poi_id']) {
			$row['poi'] = & Better_Poi_Info::getInstance($row['poi_id'])->getBasic();
			if($row['poi'] && $row['poi']['closed']){
				$row['city'] = '';
			}
		} else if ($row['last_checkin_poi']) {
			$row['user_poi'] = & Better_Poi_Info::getInstance($row['last_checkin_poi'])->getBasic();
		} else {
			$row['poi'] = array();
		}
		
		$row['location_tips'] = self::filterLocation($row);
		$row['message'] = self::dynFilterMessage($row['message']);
		($row['x'] && $row['y']) && list($row['lon'], $row['lat']) = Better_Functions::XY2LL($row['x'], $row['y']);
		($row['user_x'] && $row['user_y']) && list($row['user_lon'], $row['user_lat']) = Better_Functions::XY2LL($row['user_x'], $row['user_y']);

		if ($row['badge_id']>0) {
			$badgeId = $row['badge_id'];
			$row['badge_id'] = 0;
			
			//$badges = Better_Cache::remote()->get('badge_table');
			$badge = Better_Badge::getBadge($badgeId);
			
			if ($badge) {
				$badgeParams = $badge->getParams();
				
				$row['badge_detail'] = array(
					'name' => $badgeParams['badge_name'],
					'picture' => $badgeParams['badge_picture'],
					'big_picture'=>$badgeParams['badge_big_picture'],
					'got_tip'=>$badgeParams['got_tips']
					);
				$row['badge_id'] = $badgeId;
			}
		}
		
		$polled = 1;
		if ($row['type']=='tips' && $uid) {
			$polled = Better_Poi_Poll::getInstance($row['bid'])->isPolled($uid) ? 1 : 0;
		}
		if($row['type']=='checkin'){
			$row['message'] .= "签到";
		}
		if($haveattache){
			$row['message'] .= "上传了一张新图片";
		}
		$row['polled'] = (int)$polled;
		
		list($blog_uid, $o) = explode('.', $row['bid']);
		$row['comments'] = Better_DAO_Blogreply::getInstance($blog_uid)->getRepliesCount($row['bid']);
		
		//2011-2-10 yangl
		$row['priv_blog'] = 1;
		$row['followers'] = 0;
		$row['followings'] = 0;
		
		return $row;
	}

	protected static function preParseNickname($nickname)
	{
		
		$nickname = str_replace('\\', '\\\\', $nickname);
		$nickname = str_replace('/', '\/', $nickname);
		$nickname = str_replace('(', '\(', $nickname);
		$nickname = str_replace(')', '\)', $nickname);
		$nickname = str_replace('^', '\^', $nickname);
		$nickname = str_replace('[', '\[', $nickname);
		$nickname = str_replace(']', '\]', $nickname);
		$nickname = str_replace('*', '\*', $nickname);
		$nickname = str_replace('|', '\|', $nickname);
		$nickname = str_replace('#', '\#', $nickname);	
		$nickname = str_replace('{', '\{', $nickname);	
		$nickname = str_replace('}', '\}', $nickname);	
		$nickname = str_replace('+', '\+', $nickname);		
		$nickname = str_replace('-', '\-', $nickname);
		$nickname = str_replace('?', '\?', $nickname);
		$nickname = str_replace('.', '\.', $nickname);
		$nickname = str_replace(':', '\:', $nickname);
		$nickname = str_replace('$', '\$', $nickname);
		return $nickname;
	}
	
	/**
	 * 解析@+姓名的引用功能
	 * @param unknown_type $message
	 */
	public static function parseBlogAt($message,$wapurl=false)
	{
		$result = trim($message);
		if (strlen($result)) {
			$result = ' '.$result.' ';		
			$result = eregi_replace("　", " ", $result);	
			$result = str_replace('＠', '@', $result);
			$pat = '#(?:@|＠)([^@\s\n\r\t,:]+)[\s\n\r\t,:]*#is';			
			preg_match_all($pat, $result, $all);	
			$oldns = array_unique($all[0]);
			if (is_array($all[0]) && count($all[0])) {
				$ns = array_unique($all[1]);
				foreach ($ns as $key => $nickname) {
					$nickname_code = htmlspecialchars_decode($nickname);
					if (isset(self::$atNames[$nickname]) || isset(self::$atNames[$nickname_code])) {
						if($nickname==$nickname_code){
							$username = self::$atNames[$nickname];
						} else {
							$username = self::$atNames[$nickname_code];
						}
						$uid = Better_DAO_User::getUidByKey('username', $username);
						$str_re = '<a href="/'.$username.'">@'.$nickname.'</a> '; 	
						if($wapurl){
							$str_re ='<a href="/mobile/user?uid='.$uid.'">@'.$nickname.'</a> ';					
						}							
						$result = preg_replace('#@'.self::preParseNickname($nickname).'#is', $str_re, $result);
						
					} else {	
							if($nickname==$nickname_code){
								$username = Better_DAO_User::getUsernameByNickname($nickname);
								if($username){
									self::$atNames[$nickname] = $username;
								}
							} else {								
								$username = Better_DAO_User::getUsernameByNickname($nickname_code);
								if($username){
									self::$atNames[$nickname_code] = $username;
								}
							}							
							if($username){
								$uid = Better_DAO_User::getUidByKey('username', $username);	
								$str_re = '<a href="/'.$username.'">@'.$nickname.'</a> '; 	
								if($wapurl){
									$str_re ='<a href="/mobile/user?uid='.$uid.'">@'.$nickname.'</a> ';						
								}							
								//$result = preg_replace('#@'.self::preParseNickname($nickname).'#is', $str_re, $result);
								$result = preg_replace('#@'.self::preParseNickname($nickname).'#is', $str_re, $result);
							}
						
					}
				}
			}
			
			$result = trim($result);
		}
		
		return $result;
	}
	
/**
	 * WAP解析@+姓名的引用功能
	 * @param unknown_type $message
	 */
	public static function wapparseBlogAt($message)
	{
		$result = self::parseBlogAt($message,true);
		return $result;
		/*
		$result = trim($message);
		if (strlen($result)) {
				$result = ' '.$result.' ';	
				$result = eregi_replace("　", " ", $result);			
				$result = str_replace(array('＠'), array('@'), $result);
				$pat = '#(?:@|＠)([^@\s\n\r\t,:]+)[\s\n\r\t,:]*#is';			
				preg_match_all($pat, $result, $all);		
				$oldms = array_unique($all[0]);
				
		if (is_array($all[0]) && count($all[0])) {
				$ns = array_unique($all[1]);
				foreach ($ns as $nickname) {
					$nickname_code = htmlspecialchars_decode($nickname);
					if (isset(self::$atNames[$nickname])) {
						$username = self::$atNames[$nickname];
						$uid = Better_DAO_User::getUidByKey('username', $username);
						$result = preg_replace('#@'.self::preParseNickname($nickname).'#is', '<a href="/mobile/user?uid='.$uid.'">@'.$nickname.'</a> ', $result);
					} else {
							if($nickname==$nickname_code){
								$username = Better_DAO_User::getUsernameByNickname($nickname);
								self::$atNames[$nickname] = $username;
							} else {								
								$username = Better_DAO_User::getUsernameByNickname($nickname_code);
								self::$atNames[$nickname_code] = $username;
							}
							if($username){
								$uid = Better_DAO_User::getUidByKey('username', $username);					
								$result = preg_replace('#@'.self::preParseNickname($nickname).'#is', '<a href="/mobile/user?uid='.$uid.'">@'.$nickname.'</a> ', $result);
							}
						}
					}
				}
			}
			
			$result = trim($result);
			
		
		
		
	
		
		return $result;
		*/
	}
	
	/**
	 * api中解析@姓名
	 * 
	 * @param string $message
	 * @return array
	 */
	public static function apiParseBlogAt($message)
	{
		$result = array();
		
		$message = trim($message);
		if (strlen($message)) {
			$message = ' '.htmlspecialchars_decode($message).' ';
			//$pat = '#@([^@\s,:：]+)([\s,:：]{1})#is';
			$message = eregi_replace("　", " ", $message);	
			$message = str_replace(array('＠'), array('@'), $message);
			$pat = '#(?:@|＠)([^@\s\n\r\t,:]+)[\s\n\r\t,:]*#is';
			preg_match_all($pat, $message, $all);
			
			
			if (is_array($all[0]) && count($all[0])) {
				$ns = array_unique($all[1]);
				foreach ($ns as $nickname) {
					$nickname_code = htmlspecialchars_decode($nickname);
					if (isset(self::$atUids[$nickname])) {
						$uid = self::$atUids[$nickname];
						$result[$nickname] = $uid;
					} else {
						if ($uid=Better_DAO_User::getUidByKey('nickname', $nickname_code)) {							
							self::$atUids[$nickname] = $uid;
							$result[$nickname] = $uid;
						}
					}
				}
			}			
		}
		
		return $result;
	}
	
	protected static function replaceBlogAt($x)
	{
		
	}
	
	
	/**
	 * web格式化空消息
	 */
	public static function parseWebEmptyMsg($row){
		$return ='';
		if($row['type']=='checkin'){
			$chms = '在 '.$row['poi']['city'].' <a href="/poi/'.$row['poi']['poi_id'].'">'.$row['poi']['name'].'</a> 签到';
			$return = $row['message'] ? $chms.'：'.$row['message'] : $chms;
		}else if($row['type']=='todo'){
			$chms = '想去 '.$row['poi']['city'].' <a href="/poi/'.$row['poi']['poi_id'].'">'.$row['poi']['name'].'</a>';
			$return = $row['message'] ? $chms.'：'.$row['message'] : $chms;
		}else{
			$return = $row['message'] ? $row['message'] : ($row['attach'] ? '上传一张新图片' : ($row['upbid'] ? Better_Language::load()->javascript->global->blog->rt : ''));
		}
		
		return $return;
	}
	
}
