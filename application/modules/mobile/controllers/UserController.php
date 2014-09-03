<?php

/**
 * UserController
 * 
 * @author
 * @version 
 */

require_once 'Better/Mobile/Front.php';

class Mobile_UserController extends Better_Mobile_Front {
	
	public static $STATUS_NORMAL = "normal";
	public static $STATUS_CHECKIN = "checkin";
	public static $STATUS_TIPS = "tips";
	
	private static $maxActivities = 6;
	
	public function init()
	{
		parent::init();
		$this->needLogin();
	}
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
		//$uid = $this->getRequest()->getParam('uid');
		$uid = $_GET['uid'];
		if( $uid == NULL )
			$uid = $this->uid;
		$this->view->uid = $uid;
		$user = Better_User::getInstance($uid)->getUserInfo();	
		$this->view->isTa = ($uid == $this->uid)?True:False;	
		if( $uid != $this->uid ){
			//$this->view->isTa = True;
			Better_User_Visit::getInstance($uid)->add($this->uid);				
			if( $this->isFriend( $uid ) ){
				$this->view->isFriendLabel = $this->view->lang->user->friend->remove; 	//"删除好友";
				$this->view->isAddFriend = False;
			}else{
				$this->view->isFriendLabel = $this->view->lang->user->friend->add; 		//"添加好友";
				$this->view->isAddFriend = True;
			}
			
			if ($this->isBlocked( $uid ) ){
				//$this->view->isBlockedLabel = $this->view->lang->javascript->global->block->cancel;	//取消阻止
				$this->view->isBlocked = True;
			}else{
				if( $this->isBlocking($uid) ){
					$this->view->isBlockedLabel = $this->view->lang->javascript->global->block->cancel;	//取消阻止
					$this->view->isAddBlock = False;
				}else{
					$this->view->isBlockedLabel = $this->view->lang->user->block;
					$this->view->isAddBlock = True;
				} 
			}
		}		
		
	    $errorid = isset($_GET['err']) ? $_GET['err']: 100;

	    $errorid = strlen($errorid)>0 ? $errorid : 50;
	    
	    switch($errorid)	{
	    	case -1:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->pending;
	    		break;
	    	case 1:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->success;
	    		break;
	    	case 0:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->failed;
	    		break;
	    	case -2:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->blocked;
	    		break;
	    	case -3:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->requested;
	    		break;
	    	case -4:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->blockedby;
	    		break; 
	    	case -5:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->cantself;	
	    		break;
	    	case -6:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->invaliduser;	
	    		break;
	    	case -7:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->alreadygeo;	
	    		break;
	    	case -8:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->insufficient_karma;	
	    		break;
	    	case -9:
	    		$this->view->errorinfo = $this->lang->mobile->global->result->duplicated_request;	
	    		break;
	    	case -20:
	    		$this->view->errorinfo = $this->lang->api->error->user->insufficient_karma;	
	    		break;
	    	case 50:
	    		$this->view->errorinfo = $this->lang->mobile->global->do->sucess;	
	    		break;  
	    	case 99:
	    		$ac = Better_Registry::get('sess')->get('last_achievement');
	    		if ($ac) {
	    			$this->view->errorinfo = $ac;
	    			Better_Registry::get('sess')->set('last_achievement', '');
	    		}
	    		break;
	    	case -99:
	    		$checkinerrid = $_GET['checkinerrid'];	    		
	    		switch($checkinerrid) {
	    			case -1:
	    				$this->view->errorinfo = $this->lang->javascript->global->checkin->invalid_poi;
	    				break;
	    			case -2:
	    				$this->view->errorinfo = $this->lang->javascript->global->checkin->invalid_ll;
	    				break;
	    			case -3:
	    				$this->view->errorinfo = $this->lang->javascript->global->checkin->karma_too_low;
	    				break;
	    			case -4:
	    			case -7:
	    				$this->view->errorinfo = $this->lang->api->error->statuses->too_fast_checkin;
	    				break;
	    			case -5:
	    				$this->view->errorinfo = $this->lang->javascript->global->checkin->duplicated_checkin;
	    				break;
	    			case -6:
	    				$this->view->errorinfo = $this->lang->api->error->statues->post_need_check;
	    				break;
	    			case -8:
	    				$this->view->errorinfo = $this->lang->api->error->statues->post_same_content;
	    				break;
	    		}	    		   		
	    		break;
	    	case NULL:
	    		$this->view->errorinfo = "";	
	    		break;	
	    	case 101:
	    		$this->view->errorinfo = $this->lang->javascript->post->need_check;
	    		break;
	    	case 102:
	    		$this->view->errorinfo = $this->lang->javascript->post->forbidden;
	    		break;
	    	case 103:
	    		$this->view->errorinfo = $this->lang->javascript->post->ban_words;
	    		break;  		    
	    	case 105:
	    		$this->view->errorinfo = $this->lang->javascript->antispam->too_fast;
	    		break;
	    	case 106:
	    		$this->view->errorinfo = $this->lang->javascript->antispam->shout;
	    		break;
	    }			
	    
		$this->view->userInfo = $user;
		//zend_debug::dump($user);	
		$user_state = $user['state'];
    	if($user_state=='banned'){
			$this->_redirect('/mobile/user/close');	
    	}	
		$checkthis =  !$this->isFriend($uid) && $uid != $this->uid;		
		if($checkthis){
			$thisuser = Better_User::getInstance($uid)->getUserInfo();
			strlen($this->view->errorinfo)>0 || $this->view->errorinfo=str_replace("{NICKNAME}",$thisuser['nickname'],$this->lang->javascript->user->must_be_friend_to_see_doing);
		} else {	
			$without_me = ($this->uid==$uid) ? false : true;
			$timeline = Better_User::getInstance($this->uid)->status()->getSomebody(array(
			'page' => 1,
			'type' => array('normal', 'checkin', 'tips'),
			'page_size' => self::$maxActivities,
			'uid' => $uid,
			'without_me' => $without_me,
			'ignore_block' => true
			));
			/*		
			$timeline = Better_User::getInstance($this->uid)->blog()->getAllBlogs(array(
				'page' => 1,
				'uids' => array($uid),
				'type' => array('normal','tips','checkin'),
				'withoutme' =>$without_me,
				'ignore_block' => 'true',			
				), self::$maxActivities);
			*/
			$totalActivities = $timeline['count'];					
			for($i=0;$i<count($timeline["rows"]);$i++){		
				$timeline["rows"][$i]["message"] = Better_Blog::wapParseBlogAt($timeline["rows"][$i]["message"]);
				$timeline["rows"][$i]['showrt'] = 1;		
				if($timeline["rows"][$i]["upbid"]!="0" && strlen($timeline["rows"][$i]["upbid"])>0){					
					$upuser = explode('.', $timeline["rows"][$i]["upbid"]);
					$upuserid = $upuser[0];					
					$upuserinfo = Better_User::getInstance($upuser[0])->getUserInfo();
					$isupuserTa = ($upuserid == $this->uid)?True:False;	
					if($upuserinfo['priv_blog']=="1" && !$isupuserTa && !$this->isFollower($upuserid)){
						$timeline["rows"][$i]['showrt'] = 0;	
					}
				}
				if($timeline["rows"][$i]["type"]!='checkin' && strlen($timeline["rows"][$i]["message"])==0 && strlen($timeline["rows"][$i]["attach"])>0){
					$timeline["rows"][$i]["message"] = $this->lang->javascript->blog_with_photo_no_message;
				} else if($timeline["rows"][$i]["type"]=='normal' && strlen($timeline["rows"][$i]["message"])==0 && $timeline["rows"][$i]["upbid"]!='0'){
					$timeline["rows"][$i]["message"] = $this->lang->javascript->global->blog->rt;
				}				
			}
			$rtblogs = array();		
			foreach ($timeline["rts"] as $k => $v){
				$v['message'] =  Better_Blog::wapParseBlogAt($v['message']);
				$rtblogs[$k] = $v;
			}	
			$this->view->timeline = $timeline["rows"];			
			$this->view->retwitter = $rtblogs;							
			$lastcheckin = Better_User_Checkin::getInstance($uid)->history(1,1);
			$this->view->lastcheckin = $lastcheckin["rows"][0];
			if( $totalActivities > count($timeline['rows']))
				$this->view->hasMore = true;
			
		}
		//Zend_debug::dump($user);
		$tips = Better_User_Blog::getInstance($uid)->getAllTips($page, self::$maxActivities,$type=tips);
		$this->view->tipsnum = $tips['count'];	
		$page = 1;
		$notifications = Better_User_Notification_All::getInstance($uid)->getReceiveds(array(
			'page' => $page,
			'count' => self::$maxActivities,
			'type' => array(
				'direct_message',
				'follow_request',
				'friend_request'
			),
		));
		$this->view->notificationsnum = $notifications['count'];
		$majors = Better_User_Major::getInstance($uid)->getAll(1, 20);
		$this->view->majorsnum = (count($majors["rows"])==0)?$user["majors"]:count($majors["rows"]);
		/*
		
					
		
		$this->view->badgesnum = (Better_User_Badge::getInstance($uid)->getBadgesnum()==0) ?$user['badges']:Better_User_Badge::getInstance($uid)->getBadgesnum();
		$majors = Better_User_Major::getInstance($uid)->getAll(1, 20);
		$this->view->majorsnum = (count($majors["rows"])==0)?$user["majors"]:count($majors["rows"]);
		if($this->view->isTa)	{	
			$pois = Better_User_PoiFavorites::getInstance($uid)->all($page, self::$maxActivities);			
			$total = (int)($pois['count']);			
			$blogs = Better_User_Favorites::getInstance($uid)->allTips($page, self::$maxActivities);					
			$total += (int)($blogs['count']);				
			$blogs = Better_User_Favorites::getInstance($uid)->all($page, self::$maxActivities);				
			$total += (int)($blogs['count']);
			$this->view->favoritesnum = $total;
		}
		*/
		if($this->view->isTa)	{	
			$pois = Better_User_PoiFavorites::getInstance($uid)->all($page, self::$maxActivities);			
			$total = (int)($pois['count']);			
			$blogs = Better_User_Favorites::getInstance($uid)->allTips($page, self::$maxActivities);					
			$total += (int)($blogs['count']);				
			$blogs = Better_User_Favorites::getInstance($uid)->all($page, self::$maxActivities);				
			$total += (int)($blogs['count']);
			$this->view->favoritesnum = $total;
		}
	}
		
	/**
	 * Check if the given user is a friend or not. 
	 *
	 */
	private function isFriend( $uid ){
		
		$result = false;
		
		if( Better_User_Friends::getInstance($this->uid)->isFriend($uid) )
			$result = true;


		return $result;
	}
	
	/**
	 * Check if I am one follower of the given user 
	 *
	 */
	private function isFollower( $uid ){
		$result = false;
		
		$followers = Better_User_Follow::getInstance($uid)->getFollowers();

		if( in_array($this->uid, $followers) )
			$result = true; 

		return $result;
	}
	
	/**
	 *	Check if I am blocked by the user of $uid 
	 *
	 */
	private function isBlocked( $uid ){
		
		$result = false;
		
		$blockings = Better_User_Block::getInstance($uid)->getBlocks();

		if( $blockings &&  in_array($this->uid, $blockings) )
			$result = true;
		
		return $result;
	}
	
	private function isBlocking( $uid ){
		$result = false;
		$blockings = Better_User_Block::getInstance($this->uid)->getBlocks();
		if( $blockings && in_array($uid, $blockings) )
			$result = true;
			
		return $result;
	}
	
	public function postAction(){
		
		$uid = $_GET['uid'];
		$page = (int)($_GET['page']);
		if( $page < 1 )
			$page = 1;
		$uid = ($uid!=NULL)?$uid:$this->uid;			
		$this->view->uid = $uid;
		$this->view->isTa = ($uid == $this->uid)?True:False;
		$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();
		$checkthis =  !$this->isFriend($uid) && $uid != $this->uid;		
		if($checkthis){
			$thisuser = Better_User::getInstance($uid)->getUserInfo();
			$this->view->errorinfo=$this->lang->javascript->user->must_follow_part1.$thisuser['nickname'].$this->lang->javascript->user->must_follow_part2;
		} else {	
			$without_me = ($this->uid==$uid) ? false : true;
			$timeline = Better_User::getInstance($this->uid)->status()->getSomebody(array(
			'page' => $page,
			'type' => array('normal', 'checkin', 'tips','todo'),
			'page_size' => self::$maxActivities,
			'uid' => $uid,
			'without_me' => $without_me,
			'ignore_block' => true
			));
			/*
			$timeline = $this->user->blog()->getAllBlogs(array(
				'page' => $page,
				'uids' => array($uid),
				'type' => array('normal','tips','checkin'),
				'withoutme' =>'true',	
				), self::$maxActivities);
				*/
			$total = (int)($timeline['cnt']);						
			for($i=0;$i<count($timeline['rows']);$i++){	
				$timeline["rows"][$i]["message"] = Better_Blog::wapParseBlogAt($timeline["rows"][$i]["message"]);	
				$timeline["rows"][$i]["showrt"] = 1;		
				if($timeline["rows"][$i]["upbid"]!="0" && strlen($timeline["rows"][$i]["upbid"])>0){					
					$upuser = explode('.', $timeline["rows"][$i]["upbid"]);
					$upuserid = $upuser[0];					
					$upuserinfo = Better_User::getInstance($upuser[0])->getUserInfo();
					$isupuserTa = ($upuserid == $this->uid)?True:False;	
					if($upuserinfo['priv_blog']=="1" && !$isupuserTa && !$this->isFollower($upuserid)){
						$timeline["rows"][$i]["showrt"] = 0;	
					}
				}
				if($timeline["rows"][$i]["type"]!='checkin' && strlen($timeline["rows"][$i]["message"])==0 && strlen($timeline["rows"][$i]["attach"])>0){
					$timeline["rows"][$i]["message"] = $this->lang->javascript->blog_with_photo_no_message;
				} else if($timeline["rows"][$i]["type"]=='normal' && strlen($timeline["rows"][$i]["message"])==0 && $timeline["rows"][$i]["upbid"]!='0'){
					$timeline["rows"][$i]["message"] = $this->lang->javascript->global->blog->rt;
				}				
			}
			$this->view->timeline = $timeline['rows'];
			$rtblogs = array();		
			foreach ($timeline["rts"] as $k => $v){
				$v['message'] =  Better_Blog::wapParseBlogAt($v['message']);
				$rtblogs[$k] = $v;
			}	
			$this->view->retwitter = $rtblogs;	
			$this->view->start = ( $page - 1 ) * self::$maxActivities + 1;
			if( $page * self::$maxActivities < $total )
				$this->view->urlNext = "<a href=\"/mobile/user/post?uid=".$uid."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
			if( $page > 1 )
				$this->view->urlPrev = "<a href=\"/mobile/user/post?uid=".$uid."&page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
		}
	}
	
	public function shoutAction(){
	
		$category = $_GET['cat'];
		/*if((float)$this->user->karma<0){
			$this->view->error = $this->lang->api->error->user->insufficient_karma;	
			$this->view->buttondisable = "disabled='true'";		
		}*/ 
		//$poi_id = Better_Poi_Info::dehashId($_GET['pid']);
		//$temppoi_id = $this->lastcheckin['poi']['poi_id'];
		$thispoi_id = isset($_GET['pid']) ? $_GET['pid'] : $this->lastcheckin['poi']['poi_id'];
		$poi_id = Better_Poi_Info::dehashId($thispoi_id);
		$upbid = $_GET['upbid'];
		$placeInfo = "";
		if( $poi_id ){
			$this->view->poi_id = $poi_id;
			$place = Better_Poi_Info::getInstance($poi_id)->getBasic();
			$inpolo = "";
			$poi_list_str = Better_Config::getAppConfig()->market->polo->poi->food.",".Better_Config::getAppConfig()->market->polo->poi->film;
			$poi_list = split(",",$poi_list_str);	
			if(in_array($poi_id,$poi_list)){
				$inpolo = "<font color='red'>New Polo红切客签到地：";
			}
			$placeInfo = "@".$inpolo."<a href=\"/mobile/place?pid=".$poi_id."\">".$place['name']."</a>";			
		}
		if($upbid){
			$category = "normal";
		}
		
		
		
		switch($category){
			case self::$STATUS_CHECKIN:
				$this->view->displayTips = $this->lang->mobile->user->checkin->act.$placeInfo;
				break;
			case self::$STATUS_TIPS:
				$this->view->displayTips = $this->lang->mobile->user->tip->act.$placeInfo;
				break;
			default:
				$this->view->displayTips = $this->lang->mobile->user->shout->act.$placeInfo;
				break; 
		}		
		$this->view->error .= $_GET['error'];
		$this->view->category = $category;
				
		if( $upbid ){
			$this->view->upblog = Better_Blog::getBlog($upbid);
		}		
	}
	
	/**
	 * Accept submitted shout. 
	 *
	 */
	public function doshoutAction(){

		$post = $this->getRequest()->getPost();
		$post['message'] = trim($post['message']);
		
		$poi_id = Better_Poi_Info::dehashId($post['poi_id']);
		$photo = (is_array($_FILES) && isset($_FILES['attach'])) ? $_FILES['attach'] : null;
							
		// if it is check in, do checkin first
		$success = true;
	
		if( $success ){			
			$attach_id = '';		
			
			if ( $photo['error'] == 0 || strlen($post['message']) > 0 || ($post['type'] == 'checkin') ) {		//process only if it is not empty		
				$attach = Better_Attachment::getInstance('attach');
				
				$newFile = $attach->uploadFile('attach');
				
				if (is_object($newFile) && ($newFile instanceof Better_Attachment)) {
					$result = $newFile->parseAttachment();
				} else {
					$result = &$newFile;
				}
								
				if (is_array($result) && $result['file_id']) {
					$attach_id = $result['file_id'];
					$post['photo'] = $attach_id;
				}else if(count(explode('.', $result)) == 2){
					$attach_id = $result;
					$post['photo'] = $attach_id;
				}
				$success = true;						
			} else {
				$success = false;
			}			
			
			if( $success) {	
				$thistype = $post['type'];
				$checkinsucess = 1;
				if($post['type'] == 'checkin') {
					$thistype ='normal';
					$post['attach'] = $attach_id;
					$post['photo'] = $attach_id;
					
					$returncheckin = Better_User_Checkin::getInstance($this->uid)->checkin( $post );

					if($returncheckin['code']<0){
						$checkinsucess = 0;
					} 
				}			
				if($post['type']!='checkin' && ($photo['error'] == 0 || strlen($post['message']) > 0) && $checkinsucess  ){
					$bid = Better_Blog::post($this->uid, array(
						'message' => $post['message'],
						'upbid' => $post['upbid'],
						'attach' => $attach_id,
						'lon' => '',
						'lat' => '',
						'source' => $post['source'],
						'address' => '',
						'range' => '',
						'poi_id' => $poi_id,
						'priv' => $post['priv'],
						'type' => $thistype,
					));
				}						
			}					
		}			
		if( $success ){
			
			$this->parseAchievement();
			if($checkinsucess==0){				
				$err = "-99&checkinerrid=".$returncheckin['code'];				
			} else {
				$err = 99;
			}

			if($bid<0){
				switch ($bid){
					case -1:
						$err = 101;
						break;
					case -2:
						$err = 102;
						break;
					case -3:
						$err = 103;
						break;
					case -5:
						$err = 105;
						break;
					case -6:
						$err = 106;
						break;
				}
			}
			$this->_redirect('/mobile/user?uid='.$this->uid.'&err='.$err);			
			
		}else{
			$success = false;
			$this->view->post = $post;
			$this->view->error = "Please input ";
				//trigger_error("posting failed:");
			$redirecturl = '/mobile/user/shout?cat='.$post['type'].'&error='.$this->lang->mobile->global->postnot_null;
			if($post['upbid']){
				$redirecturl.="&upbid=".$post['upbid'];
			}
			$this->_redirect($redirecturl);							
			exit(0);
		}	
	}
	
	
	public function docheckinAction(){
		
		if( !$this->view->lastcheckin['poi']['poi_id'] )
			$this->_redirect('/mobile/search?cat=place');
	}
	
	public function crownAction(){
		
		$uid = $_GET['uid'];
		$uid = ($uid!=NULL)?$uid:$this->uid;			
		$this->view->uid = $uid;
		$this->view->isTa = ($uid == $this->uid)?True:False;	
		$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();	
		$checkthis = 0;				
		if($checkthis){		
			$thisuser = Better_User::getInstance($uid)->getUserInfo();
			$this->view->errorinfo=str_replace(array("{NICKNAME}","{TA}"),$thisuser['nickname'],$this->lang->javascript->user->must_be_friend_to_see_majors);
		} else {			
			$page = (int)($_GET['page']) <1 ? 1 :(int)($_GET['page']);
			$pagenum = 20;	
			$this->view->pagenum = $pagenum;		
			$crowns = Better_User_Major::getInstance($uid)->getAll($page, $pagenum);
			$total = $crowns['total'];					
			$this->view->crowns = $crowns['rows'];
			$this->view->page = $page;
			if( $page * $pagenum < $total ){
				$this->view->urlNext = "<a href=\"/mobile/user/crown?uid=".$uid."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
			}
			if( $page > 1 ){
				$this->view->urlPrev = "<a href=\"/mobile/user/crown?uid=".$uid."&page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
			}
		}
	}
	
	public function badgeAction(){
		
		$uid = $_GET['uid'];
		$uid = ($uid!=NULL)?$uid:$this->uid;			
		$this->view->uid = $uid;
		$this->view->isTa = ($uid == $this->uid)?True:False;
		$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();		
		$checkthis =  0;			
		if($checkthis){			
			$thisuser = Better_User::getInstance($uid)->getUserInfo();
			$this->view->errorinfo=str_replace(array("{NICKNAME}","{TA}"),$thisuser['nickname'],$this->lang->javascript->user->must_be_friend_to_see_badges);
		} else {
			$page = (int)($_GET['page']) <1 ? 1 :(int)($_GET['page']);
			$pagenum = 10;				
			$userbadges = Better_User_Badge::getInstance($uid)->getMyBadges(1, 20);		
			rsort($userbadges);	
			$total = count($userbadges);
			$startbadge = ($page-1)*$pagenum;
			$this->view->badges = array_slice($userbadges,$startbadge,$pagenum);				
			$this->view->start = ( $page - 1 ) * self::$maxActivities + 1;
			if( $page * $pagenum < $total )
				$this->view->urlNext = "<a href=\"/mobile/user/badge?uid=".$uid."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
			if( $page > 1 )
				$this->view->urlPrev = "<a href=\"/mobile/user/badge?uid=".$uid."&page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
			
			
		}
	}

	public function checkinAction(){
		$uid = $_GET['uid'];
		/*if((float)$this->user->karma<0){
			$this->view->error = $this->lang->api->error->user->insufficient_karma;	
			$this->view->buttondisable = "disabled='true'";		
		}*/ 
		$page = (int)($_GET['page']);
		if( $page < 1 )
			$page = 1;
		$uid = ($uid!=NULL)?$uid:$this->uid;			
		$this->view->uid = $uid;
		$this->view->isTa = ($uid == $this->uid)?True:False;
		$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();
		$checkthis =  !$this->isFriend($uid) && $uid != $this->uid;			
		if($checkthis){
			$thisuser = Better_User::getInstance($uid)->getUserInfo();
			$this->view->errorinfo=str_replace("{NICKNAME}",$thisuser['nickname'],$this->lang->javascript->user->must_be_friend_to_see_doing);
		}	else {	

			$without_me = ($this->uid==$uid) ? false : true;
			$checkins = Better_User::getInstance($this->uid)->status()->getSomebody(array(
			'page' => $page,
			'type' => array( 'checkin'),
			'page_size' => self::$maxActivities,
			'uid' => $uid,
			'without_me' => $without_me,
			'ignore_block' => true
			));
			/*
			$checkins = $this->user->blog()->getAllBlogs(array(
				'page' => $page,
				'uids' => array($uid),
				'type' => array('checkin'),
				'ignore_block' => 'true',
				'withoutme' =>'true',	
				), self::$maxActivities);
				*/
			$totalPages = (int)($checkins['pages']);
				
			$checkinrow = array();
        	foreach($checkins['rows'] as $rows){
        		$rows['message'] =  Better_Blog::wapParseBlogAt($rows['message']);
        		$checkinrow[] = $rows; 
        	}
			$this->view->checkins =$checkinrow;
			$this->view->start = ( $page - 1 ) * self::$maxActivities + 1;
			if( $page < $totalPages )
				$this->view->urlNext = "<a href=\"/mobile/user/checkin?uid=".$uid."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
			if( $page > 1 )
				$this->view->urlPrev = "<a href=\"/mobile/user/checkin?uid=".$uid."&page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
		}
	}
	
	public function followerAction(){
		
		$uid = $_GET['uid'];
		if($uid==BETTER_SYS_UID){
			$this->_redirect('/mobile/user');	
		} else{
			$page = (int)($_GET['page']);
			if( $page < 1 )
				$page = 1;
			$uid = ($uid!=NULL)?$uid:$this->uid;			
			$this->view->uid = $uid;
			$this->view->isTa = ($uid == $this->uid)?True:False;
			$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();		
			$checkthis = !$this->isFriend($uid);		
			if($checkthis){			
				$thisuser = Better_User::getInstance($uid)->getUserInfo();
				$this->view->errorinfo=str_replace(array("{NICKNAME}","{TA}"),$thisuser['nickname'],$this->lang->javascript->user->must_be_friend_to_see_followers);			
			} else {		
				$followers = Better_User_Follow::getInstance($uid)->getFollowersWithDetail($page, self::$maxActivities);
				$this->view->followers = $followers['rows'];
				$totalPages = (int)($followers['pages']);
				$this->view->start = ( $page - 1 ) * self::$maxActivities + 1;
				if( $page < $totalPages )
					$this->view->urlNext = "<a href=\"/mobile/user/follower?uid=".$uid."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
				if( $page > 1 )
					$this->view->urlPrev = "<a href=\"/mobile/user/follower?uid=".$uid."&page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
			}
		}
	}
	
	public function followingAction(){
		
		$uid = $_GET['uid'];
		$uid = ($uid!=NULL)?$uid:$this->uid;
		$page = (int)($_GET['page']);
		if( $page < 1 )
			$page = 1;
					
		$this->view->uid = $uid;
		$this->view->isTa = ($uid == $this->uid)?True:False;
		$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();
		$checkthis = !$this->isFriend($uid);		
		if($checkthis){			
			$thisuser = Better_User::getInstance($uid)->getUserInfo();
			$this->view->errorinfo=str_replace(array("{NICKNAME}","{TA}"),$thisuser['nickname'],$this->lang->javascript->user->must_be_friend_to_see_followings);
		} else {		
			$followings = Better_User_Follow::getInstance($uid)->getFollowingsWithDetail($page, self::$maxActivities);
			$this->view->followings = $followings['rows'];
			$totalPages = (int)($followings['pages']);
			$this->view->start = ( $page - 1 ) * self::$maxActivities + 1;
			if( $page < $totalPages )
				$this->view->urlNext = "<a href=\"/mobile/user/following?uid=".$uid."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
			if( $page > 1 )
				$this->view->urlPrev = "<a href=\"/mobile/user/following?uid=".$uid."&page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
		}
	}
		
	public function visitorAction(){
		
		$uid = $_GET['uid'];
		$uid = ($uid!=NULL)?$uid:$this->uid;			
		$this->view->uid = $uid;
		$this->view->isTa = ($uid == $this->uid)?True:False;		
		$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();
		$visitors = Better_User_Visit::getInstance($uid)->getVisitors();
		$this->view->visitors = $visitors;
	}
	
	public function friendAction(){
		$uid = $_GET['uid'];
		$uid = ($uid!=NULL)?$uid:$this->uid;
		$page = (int)($_GET['page']);
		if( $page < 1 )
			$page = 1;			
		$this->view->uid = $uid;
		$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();
		$this->view->isTa = ($uid == $this->uid)?True:False;
		$checkthis =  0;				
		if($checkthis){			
			$thisuser = Better_User::getInstance($uid)->getUserInfo();
			$this->view->errorinfo=str_replace(array("{NICKNAME}","{TA}"),$thisuser['nickname'],$this->lang->javascript->user->must_be_friend_to_see_friends);
		} else {		
			$friends = Better_User_Friends::getInstance($uid)->all($page, self::$maxActivities);
			$this->view->friends = $friends['rows'];
			$totalPages = (int)($friends['pages']);
			$this->view->start = ( $page - 1 ) * self::$maxActivities + 1;
			if( $page < $totalPages )
				$this->view->urlNext = "<a href=\"/mobile/user/friend?uid=".$uid."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
			if( $page > 1 )
				$this->view->urlPrev = "<a href=\"/mobile/user/friend?uid=".$uid."&page=".($page-1)."\">".$this->lang->mobile->pager->prev."</a>";
		}
	}
	
	public function favoriteAction(){
		
		$page = (int)($_GET['page']);
		if( $page < 1 )
			$page = 1;
		$cat = $_GET['cat'];
		if( !$cat )	$cat = "poi";			
		
		$this->view->category = $cat;
		$this->view->uid = $this->uid;
		$this->view->userInfo = $this->user->getUserInfo();	
		$this->view->start = ( $page - 1 ) * self::$maxActivities + 1;	
		$poiurl   = 	"<a href=\"/mobile/user/favorite?cat=poi\">".$this->lang->mobile->poi_favorites."</a>";
		$tipsurl  =  "<a href=\"/mobile/user/favorite?cat=tips\">".$this->lang->mobile->tips_favorites."</a>";
		$shouturl = "<a href=\"/mobile/user/favorite?cat=normal\">".$this->lang->mobile->blog_favorites."</a>";
		switch($cat)	{
			case "poi":
				$pois = Better_User_PoiFavorites::getInstance($this->uid)->all($page, self::$maxActivities);
				$this->view->favorite = $pois['rows'];		
				$total = (int)($pois['count']);	
				$poiurl= $this->lang->mobile->poi_favorites;			
			break;
			case "tips":
				$blogs = Better_User_Favorites::getInstance($this->uid)->allTips($page, self::$maxActivities);
				$blogsrow = array();
	        	foreach($blogs['rows'] as $rows){
	        		$rows['message'] =  Better_Blog::wapParseBlogAt($rows['message']);
	        		$blogsrow[] = $rows; 
	        	}
				$this->view->favorite = $blogsrow;	
			
				$total = (int)($blogs['count']);
				$tipsurl = $this->lang->mobile->tips_favorites;				
			break;			
			case "normal":				
				$blogs = Better_User_Favorites::getInstance($this->uid)->all($page, self::$maxActivities);
				$blogsrow = array();
	        	foreach($blogs['rows'] as $rows){
	        		$rows['message'] =  Better_Blog::wapParseBlogAt($rows['message']);
	        		$blogsrow[] = $rows; 
	        	}
				$this->view->favorite = $blogsrow;					
				$total = (int)($blogs['count']);
				$shouturl = $this->lang->mobile->blog_favorites;				
			break;			
		}
		$this->view->favoritemenu = $poiurl."|".$tipsurl."|".$shouturl;
		
		if( $page * self::$maxActivities < $total ){
				$this->view->urlNext = "<a href=\"/mobile/user/favorite?cat=".$cat."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
		}
		if( $page > 1 ){
				$this->view->urlPrev = "<a href=\"/mobile/user/favorite?cat=".$cat."&page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
		}	
		
	}
	
	public function addfriendAction(){
		$fid = $_GET['uid'];
		$add = $_GET['add'];
		if((float)$this->user->karma<0){
			$error = -20;	
			$this->_redirect('/mobile/user?uid='.$fid.'&err='.$error);			
			exit;
		} 
		if($fid && $fid!=BETTER_SYS_UID){
			if( $add ){
				$result = Better_User_Friends::getInstance($this->uid)->request($fid);	
				$error = $result['result'];			
			}else{		//destroy friendship
				$result = Better_User_Friends::getInstance($this->uid)->delete($fid);
				$error = $result['result'];
			}
			$this->_redirect('/mobile/user?uid='.$fid.'&err='.$error);
		}else{
			$this->_redirect('/mobile/user?uid='.$this->uid.'&err='.$error);
		}
	}
	
	public function addfollowingAction(){
		$fid = $_GET['uid'];
		$add = $_GET['add'];		
		if($fid){
			if( $add ){				
				$result = Better_User_Follow::getInstance($this->uid)->request($fid);
				$error = $result['result'];	
			}else{		//destroy friendship
				$result = Better_User_Follow::getInstance($this->uid)->delete($fid);
				$error = $result['result'];	
			}
			$this->_redirect('/mobile/user?uid='.$fid.'&err='.$error);
		}else{
			$this->_redirect('/mobile/user?uid='.$this->uid.'&err='.$error);
		}
	}
	
	public function addblockAction(){
		$uid = $_GET['uid'];
		$add = $_GET['add'];
		$newstr = $uid."--".$add;
		if( $uid ){
			if( $add ){
				Better_User_Block::getInstance($this->uid)->add($uid);				
			}else{		//destroy friendship
				Better_User_Block::getInstance($this->uid)->delete($uid);			
			}
			$this->_redirect('/mobile/user?uid='.$uid);
		}else{
			$this->_redirect('/mobile/user?uid='.$this->uid);
		}
	}
	
	public function addfavoriteAction(){
		$category = $_GET['cat'];
		$id = $_GET['id'];
		$fid = $_GET['fid'];
				
		if( 'place' == $category ){
			Better_User_PoiFavorites::getInstance($this->uid)->add($id);
			$this->_redirect('/mobile/user/favorite?cat=poi');
		}else {
			//$blog = Better_Blog::getBlog($id);
			$result = Better_User_Favorites::getInstance($this->uid)->add($id, $fid,$category);
			$this->_redirect('/mobile/user/favorite?cat='.$category);
		}
	}
	
	public function delfavoriteAction(){
		$category = $_GET['cat'];
		$id = $_GET['id'];
		
		if( 'place' == $category ){
			Better_User_PoiFavorites::getInstance($this->uid)->delete($id);
			$this->_redirect('/mobile/user/favorite?cat=poi');
		}else {
			Better_User_Favorites::getInstance($this->uid)->delete($id);
			$this->_redirect('/mobile/user/favorite?cat='.$category);
		}
	}
	
	public function delblogAction(){		
		$id = $_GET['id'];		
			Better_User_Blog::getInstance($this->uid)->delete($id);		
			$this->_redirect('/mobile/user/');		
	}
	
	public function tipsAction(){
		
		$uid = $_GET['uid'];
		$page = (int)($_GET['page']);
		if( $page < 1 )
			$page = 1;
		$uid = ($uid!=NULL)?$uid:$this->uid;		
		$error = isset($_GET['err'])?$_GET['err']:'';
		
		if($error==1){
			$this->view->errorinfo = $this->lang->javascript->poi->tips->success;
		} else if(strlen($error)){
			$this->view->errorinfo = $this->lang->javascript->poi->tips->failed;			
		}
		$this->view->uid = $uid;
		$this->view->isTa = ($uid == $this->uid)?True:False;
		$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();
		$timeline = Better_User_Blog::getInstance($uid)->getAllTips($page, self::$maxActivities,$type=tips);
		for($i=0;$i<count($timeline["rows"]);$i++){
			if($timeline["rows"][$i]["type"]!='checkin' && strlen($timeline["rows"][$i]["message"])==0 && strlen($timeline["rows"][$i]["attach"])>=0){
				$timeline["rows"][$i]["message"] = $this->lang->javascript->blog_with_photo_no_message;
			} else if($timeline["rows"][$i]["type"]!='checkin' && strlen($timeline["rows"][$i]["message"])==0 && $timeline["rows"][$i]["upbid"]!='0'){
				$timeline["rows"][$i]["message"] = $this->lang->javascript->global->blog->rt;
			}				
		}	
		$total = (int)($timeline['count']);		
		$this->view->timeline = $timeline['rows'];
		$this->view->start = ( $page - 1 ) * self::$maxActivities + 1;
		if( $page * self::$maxActivities < $total )
			$this->view->urlNext = "<a href=\"/mobile/user/tips?uid=".$uid."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
		if( $page > 1 )
			$this->view->urlPrev = "<a href=\"/mobile/user/tips?uid=".$uid."&page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
	}
	
	public function pollAction(){
		//$poiId = (int)Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$statusId = trim($_GET['status_id']);
		$option = $_GET['option'];
		$todo = $_GET['todo'];
		//$statusId = trim($this->getRequest()->getParam('status_id', ''));
		//$option = $this->getRequest()->getParam('option', '');
		$result = 0;
		$poiId = isset($_GET['poiid'])?$_GET['poiid']:'';
		//$todo = $this->getRequest()->getParam('todo', $this->do);
		
		switch($todo){
			case 'create':
				if($option=='up' || $option=='down'){
					if (Better_Blog::validBid($statusId)) {
						$data = Better_Blog::getBlog($statusId);
						$blog = &$data['blog'];
						$starterUserInfo = &$data['user'];
	
						if ($blog['type']=='tips') {
							$poll = Better_Poi_Poll::getInstance($blog['bid']);
							$row = $poll->poll(array(
								'uid' => $this->uid,
								'poll_type' => $option
								));
								
							if ($row['code'] == $row['codes']['SUCCESS']) {
								$result = 1;
							} else if ($row['code']==$row['codes']['DUPLICATED']) {
								$result = -4;
							}
						} else {
							$result = -1;//'INVALID_DATA';
						}
					} else {
						$result = -2;//'INVALID_TIPS';
					}
				} else {
					$result = -3;//'INVALID_OPTION';
				}
			break;
		}
		if($poiId>0){
			$this->_redirect('/mobile/place?pid='.$poiId."&err=".$result);
		} else {
			$this->_redirect('/mobile/user/tips?uid='.$blog['uid']."&err=".$result);
		}
	}
	public function closeAction(){		
		
	}
	public function showbadgeAction(){
		$id = $_GET['badge_id'];
		$uid = $_GET['uid'];
		$err = $_GET['err'];
		$uid = ($uid!=NULL)?$uid:$this->uid;
		$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();			
		try{		
			$this->view->badge_info = Better_User_Badge::getInstance($uid)->getBadge($id);
			$data = Better_DAO_Badge_Exchange::getInstance()->get(array(
						'badge_id' => $id
			));			
			
			$this->view->canexchanged =0;
			$this->view->hadexchanged =0;			
			if($uid == $this->uid && $data){
				if($this->view->badge_info['exchanged'])
				{
					$this->view->hadexchanged = 1;
				} else {
					$this->view->canexchanged = 1;
				}
			}			
			
		} catch(Exception $e){
			$this->view->errorinfo=$this->lang->api->error->badge->invalid_badge;
		}	
		$this->view->errorinfo=$err;	
	}
	public function exchangebadgeAction(){
		$uid = ($uid!=NULL)?$uid:$this->uid;
		$this->view->userInfo = Better_User::getInstance($uid)->getUserInfo();
		$id = $_GET['badge_id'];
		$this->view->badge_id = $id;	
	}
	public function doexchangebadgeAction(){

		$post = $this->getRequest()->getPost();
		$id = trim($post['badge_id']);
		$code = trim($post['code']);		
		$success = false;
		$data = $this->user->badge()->getBadge($id);			
		if ($data['exchanged']) {		    			
			$this->view->errorinfo=$this->lang->api->error->badge->invalid_badge;
		} else {
			$result = $this->user->badge()->exchange($id, $code);
			
			$codes = &$result['codes'];			
			switch ($result['code']) {
					case $codes['SUCCESS']:
						$this->view->errorinfo= $this->lang->api->badge->exchange_success;					
						break;
					case $codes['EXPIRED']:
						$this->view->errorinfo=$this->lang->api->error->badge->expired;						
						break;
					case $codes['NO_REMAINS_LEFT']:
						$this->view->errorinfo=$this->lang->api->error->badge->no_remains_left;										
						break;
					case $codes['EXCHANGED']:
						$this->view->errorinfo=$this->lang->api->error->badge->has_exchanged;										
						break;
					case $codes['NOT_HAVE']:
						$this->view->errorinfo=$this->lang->api->error->badge->not_have;										
						break;
					case $codes['CODE_WRONG']:
						$this->view->errorinfo=$this->lang->api->error->badge->code_wrong;									
						break;
					case $codes['FAILED']:
					default:
						$this->view->errorinfo=$this->lang->mobile->global->result->failed;	
						break;					
				}
		}
		
		$this->_redirect('/mobile/user/showbadge?badge_id='.$id.'&err='.$this->view->errorinfo);			
			
		
	}
}

