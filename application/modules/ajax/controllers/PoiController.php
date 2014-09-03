<?php
		
/**
 * POI相关
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_PoiController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init(false);	
	}	
	
	/**
	 * 搜索Poi
	 * 
	 * @return
	 */
	public function searchAction()
	{
		$ip = Better_Functions::getIP();
		$ip_ll = Better_Service_Ip2ll::parse($ip);
		$ip_lon = $ip_ll['lon'];
		$ip_lat = $ip_ll['lat'];	
		
		$lon = (float)$this->getRequest()->getParam('lon', $ip_lon);
		$lat = (float)$this->getRequest()->getParam('lat', $ip_lat);
		if ($lon == 0 && $lat == 0) {
			$lon = $ip_lon;
			$lat = $ip_lat;
		}

		$range = 50000;
		$keyword = trim($this->getRequest()->getParam('keyword', ''));
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$order = trim($this->getRequest()->getParam('order', ''));
		$withoutMine = (bool)$this->getRequest()->getParam('without_mine', 0);
		$wifiRange = (float)$this->getRequest()->getParam('wifi_range', 0);

		$result = Better_Search::factory(array(
			'what' => 'poi',
			'lon' => $lon,
			'lat' => $lat,
			'wifi_range' => $wifiRange,
			'range' => $range,
			'keyword' => $keyword,
			'query' => $keyword,
			'page' => $this->page,
			'count' => $count,
			'order' => $order,
			'without_mine' => $withoutMine,
			'method' => $this->ft() ? 'fulltext' : 'mysql'
			))->search();

		$this->output = array_merge($this->output, $result);
		$this->output['pages'] = Better_Functions::calPages($result['total'], $count);
		$this->output['page'] = $this->page;		
		$this->output['rows'] = Better_Output::filterPoiRows($this->output['rows']);

		$this->output();
	}
	
	/**
	 * 获取POI详情
	 * 
	 * @return
	 */
	public function showAction()
	{
		$poiId = (int)Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$data = array();
		
		if ($poiId>0) {
			$data = Better_Poi_Info::getInstance($poiId)->get();
		}
		
		$this->output['data'] = &$data;
		
		$this->output();
	}
	
	/**
	 * 
	 * poi内的所有吼吼
	 */
	public function poishoutsAction()
	{
		/*$poiId = Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$page = $this->getRequest()->getParam('page',1);
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		
		$data = array(
			'rows' => array(),
			'count' => 0,
			'rts' => array()
			);
			
		if ($poiId>0) {
			$userObj = $this->uid ? $this->user : Better_User::getInstance();
			$data = $userObj->status()->getSomePoi(array(
				'type' => 'normal',
				'poi' => $poiId,
				'page' => $page,
				'count' => $count,
				'page_size' => $count
				), $count);
		}

		$this->output['rows'] = Better_Output::filterBlogs($data['rows']);
		$this->output['pages'] = Better_Functions::calPages($data['count'], $count);
		$this->output['page'] = $page;
		$this->output['count'] = &$data['count'];
		$this->output['rts'] = &$data['rts'];
		*/
		
		$this->output();		
	}
	
	/**
	 * 获得POI的报到历史
	 */
	public function poicheckinsAction()
	{
		/*$poiId = Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$page = $this->getRequest()->getParam('page',1);
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		
		$data = array(
			'rows' => array(),
			'count' => 0,
			);
			
		if ($poiId>0) {
			$userObj = $this->uid ? $this->user : Better_User::getInstance();
			$data = $userObj->status()->getSomePoi(array(
				'type' => 'checkin',
				'poi' => $poiId,
				'page' => $page,
				'count' => $count,
				'page_size' => $count
				), $count);
		}

		$this->output['rows'] = Better_Output::filterBlogs($data['rows']);
		$this->output['pages'] = Better_Functions::calPages($data['count'], $count);
		$this->output['page'] = $page;
		$this->output['count'] = &$data['count'];
		*/
		
		$this->output();
	}
	
	
	/**
	 * 获得POI的好友动态
	 */
	public function poifriendstimelineAction()
	{
		$poiId = Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$page = $this->getRequest()->getParam('page',1);
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		
		$data = array(
			'rows' => array(),
			'count' => 0,
			);
			
		if ($poiId>0) {
			$userObj = $this->uid ? $this->user : Better_User::getInstance();
			$data = $userObj->status()->getSomePoi(array(
				'type' => array('checkin','normal','todo'),
				'poi' => $poiId,
				'page' => $page,
				'count' => $count,
				'page_size' => $count
				), $count);
		}

		$this->output['rows'] = Better_Output::filterBlogs($data['rows']);
		$this->output['pages'] = Better_Functions::calPages($data['count'], $count);
		$this->output['page'] = $page;
		$this->output['rts'] = Better_Output::filterBlogs($data['rts']);
		$this->output['count'] = &$data['count'];
		
		
		$this->output();
	}
	
	public function poifriendshereAction()
	{
		$poiId          = Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$page          = $this->getRequest()->getParam('page',1);
		$count         = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$friendshere = Better_User_PlaceLog::getInstance($this->uid)->friends($poiId,$page,$count);	
		
		$fblogs = array();	
		foreach($friendshere['rows'] as $row){
			$rblog = Better_DAO_Blog::getInstance($row['uid'])->getUserLastCheckin($row['uid'],$poiId);
			foreach($row as $key=>$value){
				$rblog[$key]=$value;
			}
			$rblog = Better_Blog::parseBlogRow($rblog);
			$fblogs[] = $rblog; 			
		}	
		
		$this->output['rows'] = Better_Output::filterBlogs($fblogs);
		$countarr = Better_Poi_Checkin::getInstance($poiId)->countLog(array('uid'=>$this->uid));
		$this->output['pages'] = Better_Functions::calPages($countarr['friend'], $count);
		$this->output['page'] = $page;
		$this->output['count'] = $countarr['friend'];
		$this->output['type'] = 'checkin';
		$this->output();
	}
	
	/**
	 * 获取想来这里的好友
	 */
	public function poifriendstodoAction()
	{
		$poiId  = Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$page  = $this->getRequest()->getParam('page',1);
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);	
	
		$data = array(
			'rows' => array(),
			'count' => 0,
			);			
		if ($poiId>0) {
			$userObj = $this->uid ? $this->user : Better_User::getInstance();
			$data = $userObj->status()->getFriendstodo(array(
				'type' => 'todo',
				'poi' => $poiId,
				'page' => $page,
				'page_size' => $count
				));
			$users=array();
			foreach($data['rows'] as $row){
				$users[] = Better_Blog::parseBlogRow($row);
			}
		}
		$todocount = Better_Poi_Todo::getInstance($poiId)->count(array('uid'=>$this->uid));
		$this->output['rows'] = Better_Output::filterBlogs($users);
		$this->output['pages'] = Better_Functions::calPages($todocount['friend'], $count);
		$this->output['page'] = $page;
		$this->output['count'] = $todocount['friend'];
		$this->output['type'] = 'todo';		
		$this->output();
	}
	
	
	/**
	 * 获得POI下的所有tips
	 */
	public function poitipsAction(){
		$poiId = (int)Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$tipType = $this->getRequest()->getParam('tipType', 'hot');
		$page = $this->getRequest()->getParam('page',1);
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);

		$user = $this->uid ? $this->user : Better_User::getInstance();
		$rows = Better_Poi_Tips::getRangedTips(array(
			'poi_id' => $poiId,
			'page' => $page,
			'count' => $count,
			'order' => 'poll',
			));
		
		$this->output['rows'] = Better_Output::filterBlogs($rows['rows']);
		$this->output['count'] = $rows['count'];
		$this->output['pages'] = $rows['pages'];
		$this->output['page'] = $page;
		
		$this->output();
	}
	
	/**
	 * 用户在POI上check in
	 */
	public function checkinAction(){
		$poiId = (int)Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		if (BETTER_HASH_POI_ID) {
			$poiId = Better_Poi_Info::dehashId($poiId);
		}
		
		$result=array();
		
		if($poiId>0){
			$result = Better_User_Checkin::getInstance($this->uid)->checkin(array('poi_id'=>$poiId));
		}
		
		$this->output['result'] = &$result;
		
		$this->output();
	}
	
	
	/**
	 * 举报POI
	 */
	public function reportAction(){
		$poiId = (int)Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$reason = trim($this->getRequest()->getParam('reason', ''));
		$content = trim($this->getRequest()->getParam('content',''));
		$result=array();
		
		if ($poiId>0) {
		if (!Better_Poi_Report::getInstance($poiId)->reported($this->uid, $reason)) {
				$result = Better_Poi_Report::getInstance($poiId)->report(array(
					'reason' => $reason,
					'uid' => $this->uid,
					'content'=>$content
				));
			}else{
				$result['code'] = -3;
			}
		}
		
		$this->output['result'] = &$result;
		
		$this->output();
	}
	
	/**
	 * POI投票
	 */
	public function pollAction(){
		$poiId = (int)Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$statusId = trim($this->getRequest()->getParam('status_id', ''));
		$option = $this->getRequest()->getParam('option', '');
		$result = 0;

		$todo = $this->getRequest()->getParam('todo', $this->do);
		
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
		
		$this->output['result'] = &$result;
		
		$this->output();
	}
	
	
	/**
	 * 收藏/取消收藏 POI
	 */
	public function favoritesAction()
	{
		$poiId = Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		
//		if (BETTER_4SQ_POI && $poiId && !is_numeric($poiId) && !strpos('-', $poiId)) {
//			$poiId = Better_Service_4sq_Pool::foursq2our($poiId);
//		}
		if (BETTER_AIBANG_POI && $poiId && !is_numeric($poiId)) {
			$poiId = Better_Service_Aibang_Pool::ab2our($poiId);
		}
			
		$result=array();
		
		if($poiId>0){
			$todo = $this->getRequest()->getParam('todo', $this->do);
			
			switch($todo){
				case 'create':
					$result = $this->user->PoiFavorites()->add($poiId);
					break;
				case 'destroy':
					$result = $this->user->PoiFavorites()->delete($poiId);
					break;	
				case 'list':
					$result = $this->user->PoiFavorites()->all($this->page, $this->count);
					break;
			}
		}
		
		$this->output['result'] = &$result;
		
		$this->output();
	}
	
	
	/**
	 * 修改POI基本属性(皇帝可用)
	 */
	public function updateAction(){
		$poiId = (int)Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$name = trim(urldecode($this->getRequest()->getParam('name', '')));
		$lon = (float)$this->getRequest()->getParam('lon', 0);
		$lat = (float)$this->getRequest()->getParam('lat', 0);
		$category = (int)$this->getRequest()->getParam('category', 0);
		$country = trim(urldecode($this->getRequest()->getParam('country', '')));
		$province = trim(urldecode($this->getRequest()->getParam('province', '')));
		$city = trim(urldecode($this->getRequest()->getParam('city', '')));
		$address = trim(urldecode($this->getRequest()->getParam('address', '')));
		
		$result=0;
		if ($poiId>0) {
			$poi = Better_Poi_Info::getInstance($poiId);
			if ($poi->poi_id) {
				if ($poi->major==$this->uid) {
					$name!='' && $poi->name = $name;
					$category>0 && $poi->category_id = $category;
					$country!='' && $poi->country = $country;
					$province!='' && $poi->province = $province;
					$city!='' && $poi->city = $city;
					$address!='' && $poi->address = $address;
					if ($lon && $lat) {
						list($x, $y) = Better_Functions::LL2XY($lon, $lat);
						$poi->x = $x;
						$poi->y = $y;
					}
					
					$result = $poi->update();
				} 
			} 
		} 
		
		$this->output['result'] = $result;
		
		$this->output();
	}
	
	
	/**
	 * 新增POI
	 */
	public function createAction(){
		$name = trim(urldecode($this->getRequest()->getParam('name', '')));
		$lon = (float)$this->getRequest()->getParam('lon', 0);
		$lat = (float)$this->getRequest()->getParam('lat', 0);
		$phone = trim($this->getRequest()->getParam('phone', ''));
		$category = (int)$this->getRequest()->getParam('category', 0);
		$address = trim(urldecode($this->getRequest()->getParam('address', '')));
		$city = trim(urldecode($this->getRequest()->getParam('city', '')));
		$province = trim(urldecode($this->getRequest()->getParam('province', '')));
		$country = trim(urldecode($this->getRequest()->getParam('country', '')));
		
		$result = Better_Poi_Info::create(array(
			'name' => $name,
			'lon' => $lon,
			'lat' => $lat,
			'phone' => $phone,
			'category_id' => $category,
			'address' => $address,
			'country' => $country,
			'city' => $city,
			'province' => $province,
			'creator' => $this->userInfo['uid'],
			));
			
		$this->output['result'] = &$result;
		
		$this->output();
	}
	
	public function newspecialAction(){
		$message = trim($this->getRequest()->getParam('message', ''));		
		$attach = trim($this->getRequest()->getParam('attach', ''));
		$poi_id = (int)$this->getRequest()->getParam('poi_id', 0);
		$begintm = trim($this->getRequest()->getParam('begintm', ''));
		$endtm = trim($this->getRequest()->getParam('endtm', ''));
		$nid = (int)$this->getRequest()->getParam('nid', 0);
		$result = Better_Poi_Notification::create(array(
			'poi_id' => $poi_id,			
			'creator' => $this->userInfo['uid'],
			'title' => $message,
			'content' => $message,
			'image' => $attach,
			'begintm' => $begintm,
			'endtm' => $endtm,
			'nid' => $nid,
			));
		if($result['code']==1){
			$code = 'success';
		} else {
			$code = 'failed';
		}
		$this->output['code'] = $code;
		
		$this->output();
	}
	
	public function cancelspecialAction(){
		$nid = trim($this->getRequest()->getParam('nid', ''));		
		$poi_id = (int)$this->getRequest()->getParam('poi_id', 0);
		
		$params = array(
			'nid' => $nid,
			'doing'=> 4,
			'poi_id' => $poi_id,
			'uid' =>$this->userInfo['uid']
		);
		//Zend_Debug::dump($params);
		Better_Admin_Poi::checkSpecial($params);
		
		$result['code']=1;	
		if($result['code']==1){
			$code = 'success';
		} else {
			$code = 'failed';
		}
		$this->output['code'] = $code;
		
		$this->output();
	}
		
}