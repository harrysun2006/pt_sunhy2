<?php

/**
 * HomeController
 * 
 * @author Fu Shunkai (fusk@peptalk.cn)
 * @version 
 */

require_once 'Better/Mobile/Front.php';

class Mobile_HomeController extends Better_Mobile_Front {
	
	private static $maxActivities = 6;
	public static $STATUS_FOLLOW = 'follow';
	public static $STATUS_FRIEND = 'friend';
	public static $STATUS_PUBLIC = 'public';
	private $curCat;
	
	public function init()
	{
		parent::init();
		$this->needLogin();
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
			/*
			$followurl = "<a href=\"/mobile/home?cat=".Mobile_HomeController::$STATUS_FOLLOW."\">".$this->lang->mobile->user->friend->noun."</a>";
			*/
			$friendurl =  "<a href=\"/mobile/home?cat=".Mobile_HomeController::$STATUS_FRIEND."\">".$this->lang->mobile->global->friend_action."</a>";
			$publicurl = "<a href=\"/mobile/home?cat=".Mobile_HomeController::$STATUS_PUBLIC."\">".$this->lang->global->nav->msg->rt_me."</a>";
		switch($category){
			case self::$STATUS_FOLLOW:
				$uids = $this->user->followings;
				$uids[] = $this->uid;			
				$latest = Better_User::getInstance($this->uid)->status()->newWebFollowings(array(
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
				$latest = Better_User::getInstance($this->uid)->status()->newWebFollowings(array(
					'page' => $page,
					'page_size' => self::$maxActivities,
					));				
				$friendurl = $this->lang->mobile->global->friend_action;
				//Zend_Debug::dump($this->user);
				/*
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
				 */
				break;
			case self::$STATUS_PUBLIC:
				$latest = Better_User::getInstance($this->uid)->status()->rtMine(array(
					'page' => $page,
					'page_size' => self::$maxActivities,
					));				
				$publicurl = $this->lang->global->nav->msg->rt_me;
				break;
		}
		//$latest = Better_User_Blog::getInstance($this->uid)->getFollowingsBlogs ( $pageNo, self::$maxPageSize );
		
		//$this->view->user = $this->user;
		$this->view->homemenu = $friendurl."|".$publicurl;
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
				if(strlen($latest["rows"][$i]["message"])==0) {
					$latest["rows"][$i]["message"] = $this->lang->javascript->global->blog->rt;
				}
			}
			
			if($latest["rows"][$i]["type"]!='checkin' && strlen($latest["rows"][$i]["message"])==0 && strlen($latest["rows"][$i]["attach"])>0){				
				$latest["rows"][$i]["message"] = $this->lang->javascript->blog_with_photo_no_message;		
			}
		}	
		$this->view->timeline = $latest ['rows'];	
		$this->view->retwitter = $rtblogs;
		$total = (int)($latest['count']);
		$this->view->start = ($page - 1)* self::$maxActivities + 1;
		$this->view->category = $category;
		//$this->view->numActivities = $this->view->hasNext ? self::$maxPageSize : count ( $latest ['rows'] );
		//用户删除信息后，publictimeline没有处理，导致给出的BID不存在
		if( $total > $page * self::$maxActivities && (1 ||count($latest ['rows'])>=self::$maxActivities))
	    	$this->view->urlNext = "<a href=\"/mobile/home?cat=".$category."&page=".($page + 1)."\">".$this->lang->mobile->pager->next."</a>";
	    if ( $page > 1 )
	    	$this->view->urlPrev = " <a href=\"/mobile/home?cat=".$category."&page=".($page - 1)."\">".$this->lang->mobile->pager->pre."</a>";	         
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
	public function msnAction() {
		$url = "http://blog.k.ai/live/index.php?user=kaier001@hotmail.com&pass=abc12345&b=234";
		$client = new Zend_Http_Client($url);		
		$client->request(Zend_Http_Client::GET);
		$html = $client->getLastResponse()->getBody();
		$arr = Better_Json::xml2array($html);
		Zend_Debug::dump($arr);
		/*		
		$wrap_verification_code	 = $_GET['wrap_verification_code'];
		if($wrap_verification_code){
			$url = 'https://consent.live.com/AccessToken.aspx';	
			$posturl = &$url;
			$appId =  '000000004C036708';
			$appSecret= 'Z2sWkiXaX739gfzim79koVPgj39LnQbO';
			$callbackUrl ='http://k.ai/mobile/home/msn';	
			$verificationCode = &$wrap_verification_code;	
        	$postvars = 'wrap_client_id=' . urlencode($appId)
                . '&wrap_client_secret=' . urlencode($appSecret)
                . '&wrap_callback=' . urlencode($callbackUrl)
                . '&wrap_verification_code=' . urlencode($verificationCode);
	       // $response = $this->postWRAPRequest($authUrl, $tokenRequest);
	      
			$ch = curl_init($posturl);
	        curl_setopt($ch, CURLOPT_POST, 1);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	        curl_setopt($ch, CURLOPT_HEADER, 1);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        $Rec_Data = curl_exec($ch);
	        curl_close($ch);	
	     //   return urldecode($Rec_Data);
	        
	        $response = $Rec_Data;
			$pos = strpos($response, 'wrap_access_token=');
	        if ($pos === false)
	        {
	            $pos = strpos($response, 'wrap_error_reason=');
	        }
	        $codes = '?' . substr($response, $pos, strlen($response));
	
	        //RegEx the string to seperate out the variables and thier values
	        if (preg_match_all('/[?&]([^&=]+)=([^&=]+)/', $codes, $matches))
	        {
	            for($i =0; $i < count($matches[1]); $i++)
	            {
	                //The first element in the matches array is the combination
	                //of both matches.
	                $contents[$matches[1][$i]] = $matches[2][$i];
	            }
	        }
	        Better_Log::getInstance()->logInfo(serialize($contents),'msntoken');
	        
	      
	        $uid = $contents['uid'];
	       
	        $newurl = 'http://apis.live.net/V4.1/cid-' . $uid . '/Contacts/Allcontacts';
	        //$newurl = 'http://apis.live.net/V4.1/cid-' . $uid . '/Contacts/Invitations';	      
			$client = new Zend_Http_Client($newurl);				
			$client->setHeaders('Content-Type',"application/json");			
			$client->setHeaders('Authorization',"WRAP access_token=".$contents['wrap_access_token']);		
			$client->request(Zend_Http_Client::GET);
			$html = $client->getLastResponse()->getBody();
			//$xmlObj = simplexml_load_string($html);
			//echo $html;
			try {
				$arr = Better_Json::xml2array($html);
			} catch (Exception $e) {
				$arr = array();
			}		
			
			//echo $html;
			
			Zend_Debug::dump($arr);
			$userlist = $arr['feed']['entry'];
			Zend_Debug::dump($userlist);
			
			
			
			
				
		}
		*/
		
		
	}
}

