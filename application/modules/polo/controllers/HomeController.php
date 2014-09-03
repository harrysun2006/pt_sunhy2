<?php

/**
 * HomeController
 * 
 * @author Fu Shunkai (fusk@peptalk.cn)
 * @version 
 */

require_once 'Better/Mobile/Front.php';

class Polo_HomeController extends Better_Mobile_Front {
	
	private static $maxActivities = 6;
	public static $STATUS_FOLLOW = 'follow';
	public static $STATUS_FRIEND = 'friend';
	public static $STATUS_PUBLIC = 'public';
	private $curCat;
	
	public function init()
	{
		parent::init();
		$this->poloneedLogin();
		$this->curCat = self::$STATUS_FOLLOW;
	}
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		    
		$page = (int)$_GET['page'];
		$category = $_GET['cat'];
		        	
       	if (!$page )
			$page = 1;
			
		if( !$category )
			$category = self::$STATUS_FOLLOW;			
			
			$followurl = "<a href=\"/polo/home?cat=".Polo_HomeController::$STATUS_FOLLOW."\">".$this->lang->home->my_followings."</a>";
			$friendurl =  "<a href=\"/polo/home?cat=".Polo_HomeController::$STATUS_FRIEND."\">".$this->lang->home->aroundme."</a>";
			$publicurl = "<a href=\"/polo/home?cat=".Polo_HomeController::$STATUS_PUBLIC."\">".$this->lang->mobile->user->all->noum."</a>";
		switch($category){
			case self::$STATUS_FOLLOW:
				$uids = $this->user->followings;
				$uids[] = $this->uid;			
				$latest = Better_User::getInstance($this->uid)->status()->webFollowings(array(
					'page' => $page,
					'is_following' => true,
					'type' => array('normal', 'checkin', 'tips'),
					'page_size' => self::$maxActivities
					));

				$followurl = $this->lang->home->my_followings;
				break;
			case self::$STATUS_FRIEND:
				$friends = $this->user->friends;
				$friends[] = $this->uid;
				/*
				$latest = Better_User::getInstance($this->uid)->blog()->getAllBlogs(array(
					'page' => $page,
					'uids' => $friends,
					), self::$maxActivities);
					*/
				$friendurl = $this->lang->home->aroundme;
				//Zend_Debug::dump($this->user);
				$params = array(
					'keyword' => $text,
					'page' => $page,
					'page_size' => self::$maxActivities,
					'poi' => $this->user->poi_id,
					'type' => $range,
					'without_me' => 1,
					);
				$lon = $this->user->lon;
				$lat = $this->user->lat;
				
				if ($lon && $lat) {
					$params['range'] = 5000;
					$params['lon'] = (float)$lon;
					$params['lat'] = (float)$lat;
					$params['type'] = 'normal';
				}
				//Zend_Debug::dump($params);
				//Zend_Debug::dump($params);
				$latest = $this->user->blog()->getAllBlogs($params, BETTER_PAGE_SIZE, $karmaLimit);
				//Better_Output::filterBlogs($result['rows']);
				//Zend_Debug::dump($result);
				break;
			case self::$STATUS_PUBLIC:
				$latest = $this->user->blog()->getAllPublic(array(
					'page' => $page,
					'page_size' => self::$maxActivities
					));
										
				$publicurl = $this->lang->mobile->user->all->noum;
				break;
		}
		//$latest = Better_User_Blog::getInstance($this->uid)->getFollowingsBlogs ( $pageNo, self::$maxPageSize );
		
		//$this->view->user = $this->user;
		$this->view->homemenu = $followurl."|".$friendurl."|".$publicurl;
		$rtblogs = array();		
		foreach ($latest["rts"] as $k => $v){
			$v['message'] =  Better_Blog::wapParseBlogAt($v['message']);
			$rtblogs[$k] = $v;
		}		
		for($i=0;$i<count($latest["rows"]);$i++){		
			$latest["rows"][$i]["message"] = Better_Blog::wapParseBlogAt($latest["rows"][$i]["message"]);		
			$latest["rows"][$i]['showrt'] = 1;		
			if($latest["rows"][$i]["upbid"]!="0" && strlen($latest["rows"][$i]["upbid"])>0){					
				$upuser = explode('.', $latest["rows"][$i]["upbid"]);
				$upuserid = $upuser[0];					
				$upuserinfo = Better_User::getInstance($upuser[0])->getUserInfo();
				$isupuserTa = ($upuserid == $this->uid)?True:False;	
				if($upuserinfo['priv_blog']=="1" && !$isupuserTa && !$this->isFollower($upuserid)){
					$latest["rows"][$i]['showrt'] = 0;	
				}
			}
			
			if($latest["rows"][$i]["type"]!='checkin' && strlen($latest["rows"][$i]["message"])==0 && strlen($latest["rows"][$i]["attach"])>=0){
				$latest["rows"][$i]["message"] = $this->lang->javascript->blog_with_photo_no_message;
			} else if($latest["rows"][$i]["type"]!='checkin' && strlen($latest["rows"][$i]["message"])==0 && strlen($latest["rows"][$i]["upbid"])!='0'){
				$latest["rows"][$i]["message"] = $this->lang->javascript->global->blog->rt;
			}			
		}	
		$this->view->timeline = $latest ['rows'];
		
		$this->view->retwitter = $rtblogs;
		$total = (int)($latest['count']);
		$this->view->start = ($page - 1)* self::$maxActivities + 1;
		$this->view->category = $category;
		//$this->view->numActivities = $this->view->hasNext ? self::$maxPageSize : count ( $latest ['rows'] );
		if( $total > $page * self::$maxActivities )
	    	$this->view->urlNext = "<a href=\"/polo/home?cat=".$category."&page=".($page + 1)."\">".$this->lang->mobile->pager->next."</a>";
	    if ( $page > 1 )
	    	$this->view->urlPrev = " <a href=\"/polo/home?cat=".$category."&page=".($page - 1)."\">".$this->lang->mobile->pager->pre."</a>";	   
	    $cityInfo =Better_Functions::getip2city();
	    $this->view->poloacturl = '/polo/place?pid='.Better_Config::getAppConfig()->market->polo->poi->bj;
	    if($cityInfo['live_city']=='上海'){
	    	$this->view->poloacturl = '/polo/place?pid='.Better_Config::getAppConfig()->market->polo->poi->sh;
	    }  else if($cityInfo['live_city']=='广州'){
	    	$this->view->poloacturl = '/polo/place?pid='.Better_Config::getAppConfig()->market->polo->poi->gz;
	    } 
	}
	private function isFollower( $uid ){
		$result = false;
		
		$followers = Better_User_Follow::getInstance($uid)->getFollowers();

		if( in_array($this->uid, $followers) )
			$result = true; 

		return $result;
	}
	public function kaiAction() {
			
			
	}
}

