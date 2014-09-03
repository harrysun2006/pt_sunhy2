<?php

/**
 * SearchController
 * 
 * @author
 * @version 
 */

require_once 'Better/Mobile/Front.php';

class Polo_SearchController extends Better_Mobile_Front {
	
	private static $maxPageSize = 6;
	
	public function init()
	{
		parent::init();
		$this->poloneedLogin();
	}
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
		$search_city = (isset($_COOKIE['search_city']) && $_COOKIE['search_city']!='')? $_COOKIE['search_city'] : '';
		$search_province = (isset($_COOKIE['search_province']) && $_COOKIE['search_province']!='')? $_COOKIE['search_province'] : '';
		//搜索定位逻辑：先查看是否有COOKIE，然后根据IP定位，然后再看用户的城市属性，最后是 直接定位到北京
		if(!$search_city)
		{
			$cityInfo =Better_Functions::getip2city();
			$search_province = $cityInfo['live_province'];
			$search_city = $cityInfo['live_city'];
			if($search_province=='未知' || $search_city=='未知'){
				$search_province = $this->userInfo['live_province'];
				$search_city = $this->userInfo['live_city'];
			}	
			if($search_province=='未知' || $search_city=='未知'){
				$search_province = '北京市';
				$search_city = '北京';
			}		
			setcookie("search_province", $search_province,time()+Better_Session_Base::$stickTime, '/');
			setcookie("search_city", $search_city,time()+Better_Session_Base::$stickTime, '/');						
		}
		$this->view->search_city = $search_city;
		$thiv->view->search_province = $search_province;
		$cityll = Better_Citycenterll::$citycenterll;
		$ll = $cityll[$search_province][$search_city];
		$params = $this->getRequest()->getParams();		
		if( strlen(trim($params['q'])) ){			
			switch ($params ['cat']) {
				case "place" :
					$params['byAround'] = 'on';
					$params['lon'] = $ll['1'];
					$params['lat'] = $ll['0'];
					$this->view->results = $this->placeSearch( $params );
					break;
				case "user" :
					$this->view->results = $this->userSearch( $params );
					break;
				case "status" :
					break;
			}
			$this->view->cat = $params ['cat'];
			$this->view->results ['params'] = $params;
			$page = (int)($params['page']);
			$total = (int)($this->view->results['total']);
			$this->view->results['params']['start'] = ($page - 1) * self::$maxPageSize + 1;
			$this->view->results['params']['hasNext'] = ($page * self::$maxPageSize < $total)?true:false;
			$this->view->results['params']['hasPrev'] = ($page > 1)?true:false;
			$this->view->results['params']['urlNext'] = $this->urlNext( $params );
			$this->view->results['params']['urlPrev'] = $this->urlPrev( $params );
			
		}else if(isset($params['q'])){
				
			$this->view->err = array(
				'has_err' => 1,
				'err' => $this->lang->api->error->statuses->query_required,
			);			
			echo $this->view->render('search/index.phtml');
			exit(0);
		}
	}
	
	private function placeSearch( $params ){	
		$poi_id = (int)Better_Poi_Info::dehashId($params['poi_id']);
		$byAround = $params['byAround'];
		$page = $params['page'];	
		$results = array();
		$query = array();
		$query['keyword'] = $params['q'];
		$query['page'] = $params['page'];
		$query['count'] = self::$maxPageSize;
		$query['lon'] = $params['lon'];
		$query['lat'] = $params['lat'];
		$query['range'] = 50000;
		/*
		if( "on" == $byAround ){
			$query['lat'] = $this->lastcheckin['lat'];
			$query['lon'] = $this->lastcheckin['lon'];
		}
		*/
		if ($this->config->poi->fulltext->enabled) {
			$query['what'] = 'poi';
			$query['method'] = 'fulltext';
			$result = Better_Search::factory($query)->search();
			
			if (count($result['rows'])==0) {
				$query['q'] = 'more:('.$query['keyword'].')';
				$query['range'] = 99999999;
				$result = Better_Search::factory($query)->search();
			}
		} else {
			$result = Better_DAO_Poi_Search::getInstance()->search($query);
		}
		
		return $result;
	}
	
	private function userSearch($params){
		
		$page = $params['page'];
		$keyword = $params['q'];
		$count = self::$maxPageSize;
		$result = Better_Search::factory(array(
			'what' => 'user',
			'page' => $page,
			'keyword' => $keyword,
			'count' => $count,
			))->search();				
		return $result;
	}
	
	private function statusSearch($params){
		
	}
	
	private function urlNext( $params ){
		
		$result = "/polo/search?";
		$result = $result."byAround=".$params['byAround'];
		if( $params['byAround'] == "on" )
			$result = $result."&poi_id=".((int)Better_Poi_Info::dehashId($params['poi_id']));
		$result = $result."&q=".urlencode($params['q']);
		$result = $result."&cat=".$params['cat'];
		$result = $result."&page=".((int)($params['page'])+1);
		
		return $result;
	}
	
	private function urlPrev( $params ){
		
		$result = "/polo/search?";
		$result = $result."byAround=".$params['byAround'];
		if( $params['byAround'] == "on" )
			$result = $result."&poi_id=".((int)Better_Poi_Info::dehashId($params['poi_id']));
		$result = $result."&q=".urlencode($params['q']);
		$result = $result."&cat=".$params['cat'];
		$result = $result."&page=".((int)($params['page'])-1);
		
		return $result;
	}

	public function changeprovinceAction() {		
		$post = $this->getRequest()->getPost();
		$return = array('has_err'=>0, 'err'=>'');		
		$data = array(
			'live_province' => $post['live_province'],			
		);
		$checked = 0;		
		$this->view->cityArray =Better_Citycenterll::$cityArray;
		foreach($this->view->cityArray as $rows){		
			if($rows['0']==$post['live_province']){
				$checked = 1;
				setcookie("search_province", $post['live_province'],time()+Better_Session_Base::$stickTime, '/');				
				break;
			}
		}
		if($checked){			
			$return['has_err'] = 0;		
			$this->_redirect('/polo/search/changecity');
		} 
		return $return;
	}
	public function changecityAction() {
		$search_province = (isset($_COOKIE['search_province']) && $_COOKIE['search_province']!='')? $_COOKIE['search_province'] : '';	
		$this->view->cityArray =Better_Citycenterll::$cityArray;
		if(!$search_province){
			$this->_redirect('/polo/search/changeprovince');
		}		
		foreach($this->view->cityArray as $rows){	
			if($rows['0']==$search_province){
				$temp_city = split("\|",$rows[1]);				
				$this->view->city = $temp_city;
				break;
			}			
		}			
		$post = $this->getRequest()->getPost();
		$return = array('has_err'=>0, 'err'=>'');		
		$data = array(
			'live_city' => $post['live_city'],			
		);
		$checked = 0;	
		foreach($this->view->cityArray as $rows){	
			if($rows['0']==$search_province){
				$temp_city = split("\|",$rows[1]);				
				for($j=0;$j<count($temp_city);$j++){
					$a =strpos($post['live_city'],$temp_city[$j]);				
					if($a === false)
					{
						
					} else {
						$checked = 1;
						break;
					}
				}				
			}			
		}
		if($checked){
			setcookie("search_city", $post['live_city'],time()+Better_Session_Base::$stickTime, '/');	
			$this->_redirect('/polo/search/');
		} 
		return $return;
	}
}

