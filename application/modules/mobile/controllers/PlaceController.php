<?php

/**
 * PlaceController
 * 
 * @author
 * @version 
 */

require_once 'Better/Mobile/Front.php';

class Mobile_PlaceController extends Better_Mobile_Front {
	
	public static $maxNumVisitors = 20;
	public static $maxNumActivities = 6;
	
	public function init()
	{
		parent::init();
		//$this->needLogin();
	}
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
		//$this->view->userInfo = $this->user->getUserInfo();
		$poi_id = Better_Poi_Info::dehashId($_GET['pid']);
		$place = Better_Poi_Info::getInstance($poi_id)->get();
		if($place['closed']==1){
			//$this->view->errorinfo = $this->lang->global->poi_closed;
			$this->_redirect('/mobile/place/close');
		} else {			
			$this->view->place = $place;
			//Zend_Debug::dump($this->view->place);
			$error = isset($_GET['err'])?$_GET['err']:'';		
			if($error==1){
				$this->view->errorinfo = $this->lang->javascript->poi->tips->success;
			} else if(strlen($error)){
				$this->view->errorinfo = $this->lang->javascript->poi->tips->failed;			
			}
			$tips = Better_Poi_Tips::getInstance($poi_id)->all(1,self::$maxNumActivities);
			for($i=0;$i<count($tips["rows"]);$i++){
				$tips["rows"][$i]["message"] = Better_Blog::wapParseBlogAt($tips["rows"][$i]["message"]);
				if($tips["rows"][$i]["type"]!='checkin' && strlen($tips["rows"][$i]["message"])==0 && strlen($tips["rows"][$i]["attach"])>=0){
						$tips["rows"][$i]["message"] = $this->lang->javascript->blog_with_photo_no_message;
				} else if($tips["rows"][$i]["type"]!='checkin' && strlen($tips["rows"][$i]["message"])==0 && $tips["rows"][$i]["upbid"]!='0'){
						$tips["rows"][$i]["message"] = $this->lang->javascript->global->blog->rt;
				}
			}	
			$this->view->tips = $tips['rows']; 
			$totalPages = (int)($tips['pages']);
			if( $totalPages > 1 )
				$this->view->hasMore = true;
		}
	}
	
	public function visitorAction(){
		$poi_id = Better_Poi_Info::dehashId($_GET['pid']);
		$place = Better_Poi_Info::getInstance($poi_id)->get();
		$this->view->place = $place;
		$visitors = Better_Poi_Checkin::getInstance($poi_id)->users(1,self::$maxNumVisitors);
		$this->view->visitors = $visitors['rows'];
	}
	
	public function favoriteAction(){
		$poi_id = Better_Poi_Info::dehashId($_GET['pid']);
		$place = Better_Poi_Info::getInstance($poi_id)->get();
		$this->view->place = $place;
		$favorite = Better_Poi_Favorites::getInstance($poi_id)->getUsers(1, self::$maxNumVisitors);
		//Zend_Debug::dump($favorite);
		$this->view->favorite = $favorite['rows'];
	}
	
	public function introductionAction(){
		$poi_id = Better_Poi_Info::dehashId($_GET['pid']);
		$place = Better_Poi_Info::getInstance($poi_id)->get();
		$this->view->place = $place;
		
	}
	
	public function tipsAction(){
		$poi_id = Better_Poi_Info::dehashId($_GET['pid']);
		$page = $_GET['page'];
		if( $page < 1 )
			$page = 1;
		$place = Better_Poi_Info::getInstance($poi_id)->get();
		$this->view->place = $place;
		$tips = Better_Poi_Tips::getInstance($poi_id)->all($page,self::$maxNumActivities);
		for($i=0;$i<count($tips["rows"]);$i++){
			$tips["rows"][$i]["message"] = Better_Blog::wapParseBlogAt($tips["rows"][$i]["message"]);
			if($tips["rows"][$i]["type"]!='checkin' && strlen($tips["rows"][$i]["message"])==0 && strlen($tips["rows"][$i]["attach"])>=0){
				$tips["rows"][$i]["message"] = $this->lang->javascript->blog_with_photo_no_message;
			} else if($tips["rows"][$i]["type"]!='checkin' && strlen($tips["rows"][$i]["message"])==0 && $tips["rows"][$i]["upbid"]!='0'){
				$tips["rows"][$i]["message"] = $this->lang->javascript->global->blog->rt;
			}
		}	
		$this->view->tips = $tips['rows']; 
		$totalPages = (int)($tips['pages']);
		$this->view->start = ( $page - 1 ) * self::$maxNumActivities + 1;
		if( $page < $totalPages )
			$this->view->urlNext = "<a href=\"/mobile/place/tips?pid=".$poi_id."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
		if( $page > 1 )
			$this->view->urlPrev = "<a href=/mobile/place/tips?pid=".$poi_id."&page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
	}
	
	public function nearbyusersAction(){
		
		$poi_id = Better_Poi_Info::dehashId($_GET['pid']);
		$page = $_GET['page'];
		$place = Better_Poi_Info::getInstance($poi_id)->get();
		$this->view->place = $place;
		$lat = $place['lat'];
		$lon = $place['lon'];
		$range = 5000;
		if( !$lat && !$lon ){
			$this->view->err = array( 'has_err'=>0, 'err'=>$this->lang->global->lbs->unknown_address );
			echo $this->view->render('/mobile/place/nearbyusers');
			exit(0);
		}
		
		$method = 'mysql';
		$query = 'k';

		$result = Better_Search::factory(array(
			'what' => 'user',
			'page' => $page,
			'count' => self::$maxNumActivities,
			'keyword' => $query,
			'lon' => $lon,
			'lat' => $lat,
			'range' => $range,
			'method' => $method,
			))->search();
		
		$this->view->users = $users['rows'];
		$total = $users['total'];
		if( $page * self::$maxNumActivities < $total )
			$this->view->urlNext = "<a href=\"/mobile/place/nearbyusers?pid=".$poi_id."&page=".($page+1)."\">".$this->lang->mobile->pager->next."</a>";
		if( $page > 1 )
			$this->view->urlPrev = "<a href=/mobile/place/nearbyusers?pid=".$poi_id."&page=".($page-1)."\">".$this->lang->mobile->pager->pre."</a>";
	}
	public function closeAction(){
		
	}
	
	public function poloAction() {
		//Better_Config::getAppConfig()->market->polo->poi->food;
		$poi_list_food = Better_Config::getAppConfig()->market->polo->poi->food;
		$poi_list_film = Better_Config::getAppConfig()->market->polo->poi->film;
		$this->view->poi_food_list = split(",",$poi_list_food);
		$this->view->poi_film_list = split(",",$poi_list_film);
		$poi_list = $poi_list_food.",".$poi_list_film;
		$result = Better_DAO_Blog_Checkin::getlotspoiCheckin(array(
				'poi_id' => $poi_list,
				'page_size' => '6',
				'page' => '1'
			));		
		$this->view->checkinlist = $result;
	}
	public function morepoloAction(){
		$poi_list_food = Better_Config::getAppConfig()->market->polo->poi->food;
		$poi_list_film = Better_Config::getAppConfig()->market->polo->poi->film;
		$this->view->poi_food_list = split(",",$poi_list_food);
		$this->view->poi_film_list = split(",",$poi_list_film);
		$this->view->type=$_GET['type'];		
	}	
}

