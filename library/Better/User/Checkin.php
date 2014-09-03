<?php

/**
 * 用户Checkin
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Checkin extends Better_User_Base
{
	protected static $instance = array();
	protected $lastPoiId = 0;

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}
	
	/**
	 * 在某个Poi报到
	 * 
	 * @param $poiId
	 * @return array
	 */
	public function checkin(array $params)
	{
		$codes = array(
			'ERROR' => 0,
			'INVALIDPOI' => -1,
			'INVALIDLL' => -2,
			'SUCCESS' => 1,
			'KARMA_TOO_LOW' => -3,
			'TOO_FAST_CHECKIN' => -4,
			'DUPLICATED_CHECKIN' => -5,
			'FORBIDDEN_WORDS' => -6,
			'TOO_FAST_POST' => -7,
			'POST_SAME_CONTENT' => -8,
			'YOU_R_MUTED'=> -9,
			'SCODE'=> -10,
			'SCODE_ERROR'=> -11,
			);
		$result = array(
			'code' => $codes['ERROR'],
			'codes' => &$codes,
			);
		
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$poiId = $params['poi_id'];
		$abId = $params['aibang_id'] ? $params['aibang_id'] : $params['abid'];
		$priv = isset($params['priv']) ? $params['priv'] : 'public';
		$message = isset($params['message']) ? trim($params['message']) : '';
		$photo = isset($params['attach']) ? trim($params['attach']) : '';
		$checkin_need_sync = isset($params['checkin_need_sync']) ? $params['checkin_need_sync'] : 1;
		$is_tips = $params['is_tips'] == 'true' ? 1 : 0;
		$scode = $params['scode'];
		$inPolo = false;
		
		if ( !defined('IN_API') ) {
			//缓存+1
			$memcache = Better_Cache::remote();
			$key = 'uid_scode_' .  $this->uid;
			$var = $memcache->get($key);
			if ($var >= 5) {
				if ($scode) {
					$authCode = Better_Registry::get('sess')->get('authCode');
					if ( $scode != $authCode ) {
						$result['code'] = $codes['SCODE_ERROR'];
						return $result;						
					} 					
				} else {
					$result['code'] = $codes['SCODE'];
					return $result;
				}
				
			}									
		}
		
		if (!$lon || !$lat) {
			$cached = Better_DAO_Lbs_Cache::getInstance()->get(array(
				'uid' => $this->uid
				));
			if (trim($cached['lon']) && trim($cached['lat'])) {
				$lon = trim($cached['lon']);
				$lat = trim($cached['lat']);
			}
		}
		
		$source = strtolower($params['source']);
		$this->getUserInfo();
		
		if($this->userInfo['state']==Better_User_State::MUTE){
			$result['code'] = $codes['YOU_R_MUTED'];
		}else{
//		if ($this->userInfo['karma']>=0) {
			$antiSpam = Better_Config::getAppConfig()->anti_spam->enable;
			if ($antiSpam) {
				$offset = (int)Better_Config::getAppConfig()->anti_spam->offset;
				
				$cacheMsg = $this->user->cache()->get('last_message');
				$lastMsg = $cacheMsg['message'];
				$lastPost = $cacheMsg['dateline'];

				if (time()-$lastPost<$offset) {
					$result['code'] = $codes['TOO_FAST_CHECKIN'];
				} else if (trim($lastMsg) && trim($message) && trim($lastMsg)==trim($message)) {
					$result['code'] = $codes['POST_SAME_CONTENT'];
				}				
			}
			
			if ($result['code']!=$codes['TOO_FAST_CHECKIN'] && $result['code']!=$codes['TOO_FAST_POST'] && $result['code']!=$codes['POST_SAME_CONTENT']) {
				if (BETTER_AIBANG_POI && !$poiId && $abId) {
					$poiId = Better_Service_Aibang_Pool::ab2our($abId);
				}
				
				if ($poiId>0) {
					$poiInfo = Better_Poi_Info::getInstance($poiId);
		
					if ($poiInfo->poi_id) {				
						$lct = Better_DAO_User_PlaceLog::getInstance($this->uid)->getLastCheckinedAtPoi($poiId);
						
						$f = time()- 600 > $lct;
						//把市场特殊的POI签到要求列出来POI_ID|BEGTM|ENDTM|distance
						$marketpoistr = Better_Config::getAppConfig()->marketcheckin->require;
						$marketpoi = array();
						$marketpoi_list = split('#',$marketpoistr);
						$nowtime = time();
						$special_checkin = 0;
						if(is_array($marketpoi_list) && strlen($marketpoi_list[0])>0){										
							foreach($marketpoi_list as $row){											
								$temp_market = split('\|',$row);
								$a1 = $temp_market[1];
								$a2 = $temp_market[2];
								$a3 = $temp_market[3];								
								if($poiId==$temp_market[0] && $nowtime>=$temp_market[1] && $nowtime<=$temp_market[2] && ($nowtime-$temp_market[3])>$lct){									
									$f = 1;
									$marketdistance = $temp_market[4];						
									$special_checkin =1;
									break;
								}
							}						
						}				
						if ($f) {
							!$special_checkin && $fiveMinutesCheckins = Better_DAO_User_PlaceLog::getInstance($this->uid)->getFiveMinutesCheckinCount();
							list($x, $y) = Better_Functions::LL2XY($lon, $lat);
							
							$hooks = array();
							$hooks[] = 'Blog';
								
							$lang = Better_Registry::get('lang');
							$lang || $lang = Better_Language::load();
							
							if (!$special_checkin && $fiveMinutesCheckins>=3 && APPLICATION_ENV == 'production') {
								$score = 0;	
								$distance = 99999999;
								$checkins = 99999999;
								Better_Log::getInstance()->logInfo($lang->global->too_fast_checkin, 'checkin_exception');
							} else {
								
								$checkins = Better_DAO_User_PlaceLog::getInstance($this->uid)->getTodayValidCheckinCount();
								$distance = -1;
								
								if ($poiInfo->lon && $poiInfo->lat) {
									$distance = Better_Service_Lbs::getDistance($lon, $lat, $poiInfo->lon, $poiInfo->lat);
								}	
								$lasttohere = 1;
								if($this->userInfo['last_checkin_poi'])	{
									$lastpoiInfo = Better_Poi_Info::getInstance($this->userInfo['last_checkin_poi']);
									$lasttohere = Better_Service_Lbs::getDistance($lastpoiInfo->lon, $lastpoiInfo->lat, $poiInfo->lon, $poiInfo->lat);
									$lasttohere = $lasttohere>0 ? $lasttohere:1;
								}						
								if($special_checkin){
									$score = 0;
									if($distance<=$marketdistance && $distance != -1){
										$score =100;
									} 									
								} else {								
									$score = $this->_checkinScore(array(
										'distance' => $distance,
										'poi_id' => $poiId,
										'checkins' => $checkins,
										'lon' => $lon,
										'lat' => $lat,		
										'lasttohere' => $lasttohere,					
										));	
								}

								//活动	
								$in_event = Better_Market_Event::getInstance()->inEvent($poiInfo->poi_id);		
								$day = 0;
								$blacklist = array();
								if ($in_event) {
									$day = Better_Market_Event::getInstance()->day;
									$blacklist = Better_Market_Event::getInstance()->getMajor();									
									if ($distance > 0 && $distance < Better_Market_Event::getInstance()->distance ) {
										$score = 100;
									} else {
										$score = 0;
									}
									
									//非客户端签到 全部判断为无效
									if (!defined('IN_API')) $score = 0;	
									//黑名单全部设置 0
									if (in_array($this->uid, $blacklist)) $score = 0;	
								}
								//end
							}

							
							if ($priv=='public') {
								if ($score>0) {
									$hooks[] = 'Major';
									$hooks[] = 'Karma';
									$hooks[] = 'Badge';		
									$hooks[] = 'Syncsites';															
								}
								
								if ($in_event && !in_array('Badge', $hooks)) {
									$hooks[] = 'Badge';
								}
								
								$hooks[] = 'Poi';
							} 
							
							$city = '';
							if ($score>0) {
								$geo = new Better_Service_Geoname();
								$geo_info = $geo->getGeoName($lon,$lat);
								$city = $geo_info['name'];
								$hooks[] = 'Rp';	
							}
							
							$checkinId = Better_DAO_User_PlaceLog::getInstance($this->uid)->insert(array(
								'uid' => $this->uid,
								'poi_id' => $poiId,
								'x' => $x,
								'y' => $y,
								'checkin_time' => time(),
								'checkin_score' => $score,
								'distance' => $distance,
								'city' => $city
								));	

							//签到过的POI
							Better_DAO_Poi_Checkin::getInstance()->insert(array('poi_id'=>$poiId, 'dateline'=>time()));
			
							$hooks[] = 'User';
							$hooks[] = 'Cache';
							$hooks[] = 'Clean';
							if ($priv=='public') {
								$hooks[] = 'Market';
							}

							Better_Hook::factory($hooks)->invoke('UserCheckin', array(
								'checkin_id' => $checkinId,
								'uid' => $this->uid,
								'poi_id' => $poiId,
								'poi_x' => $poiInfo->x,
								'poi_y' => $poiInfo->y,
								'checkin_time' => time(),
								'checkins' => $checkins,
								'distance' => $distance,
								'priv' => $priv,
								'score'=> $score,
								'source' => isset($params['source']) ? $params['source'] : 'kai',
								'x' => $x,
								'y' => $y,
								'message' => $message,
								'photo' => $photo,
								'checkin_need_sync'=> $checkin_need_sync,
								'day'=> $day,
								'blacklist'=> $blacklist,
								'is_tips'=> $is_tips,
							));
							
							$nbid = Better_Hook::$hookResults['UserCheckin']['bid'];
							switch ($nbid) {
								case -1:
									$result['code'] = $codes['FORBIDDEN_WORDS'];
									break;
								default:
									$this->lastPoiId = $poiId;
									$hookResult = Better_Hook::getResult('UserCheckin');
									$result['karma'] = $hookResult['karma'];
									$result['badge'] = $hookResult['badge'];
									$result['fblog'] = $hookResult['fblog'];
									$result['notify'] = Better_Hook::getNotify('UserCheckin');								
									$result['city'] = $poiInfo->city;
									$result['poi_id'] = (int)$poiId;
									$result['poi_name'] = $poiInfo->name;
									$result['address'] = $poiInfo->address;
									$result['score'] = $score;
									$result['code'] = $codes['SUCCESS'];
									
									if ( !defined('IN_API') ) {
										//缓存+1
										$memcache = Better_Cache::remote();
										$key = 'uid_scode_' .  $this->uid;
										$var = $memcache->get($key);
																			
										if ($var) {
											$new_var = $memcache->increment($key, 1);
										} else {
											$a = $memcache->set($key, 1, 180);
										}
									}
									break;
							}
	
						} else {
							$result['code'] = $codes['DUPLICATED_CHECKIN'];
						}
				
					} else {
						$result['code'] = $codes['INVALIDPOI'];
					}			
				} else {
					if (BETTER_AIBANG_POI && $abId) {
						
					} else {
						$result['code'] = $codes['INVALIDPOI'];
					}
				}
			}
		}
//		} else {
//			$result['code'] = $codes['KARMA_TOO_LOW'];
//		}

		return $result;
	
	}
	/**
	 * 
	 * 解析上一次签到的一些数据
	 * 
	 * @return array
	 */
	public function parseLastCheckin()
	{
		$result = array(
			'my_today_visits' => 1,
			'my_today_poi_visits' => 1,
			'poi_today_visits' => 1,
			'my_poi_visits' => 1,
			'poi_id' => $this->lastPoiId
			);
			
		if ($this->lastPoiId) {
			$poiInfo = Better_Poi_Info::getInstance($this->lastPoiId)->get();
			$dao = Better_DAO_User_PlaceLog::getInstance($this->uid);

			$row = $dao->getMyCheckinCount($this->lastPoiId);
			
			$result['my_poi_visits'] = $row['total'];
			$result['my_today_visits'] = $dao->getTodayCheckinCount();
			
			if ($result['my_today_visits']>1) {
				$result['my_today_poi_visits'] = $dao->getTodayCheckinCount($this->lastPoiId);
			}
			
			$result['poi_today_visits'] = Better_DAO_User_PlaceLog::sGetTodayCheckinCount($this->lastPoiId);;
		}
		
		return $result;
	}
	
	/**
	 * 
	 * 在某个poi的签到次数
	 * @param unknown_type $poiId
	 */
	public function checkinsAtPoi($poiId)
	{
		$dao = Better_DAO_User_PlaceLog::getInstance($this->uid);
		
		$poiId || $poiId = $this->lastPoiId;
		$row = $dao->getMyCheckinCount($poiId);
		
		return (int)$row['total'];		
	}
	
	
	/**
	 * 某poi最后一次签到时间
	 */
	public function lastCheckinTimeAtPoi($poiId){
		$dao = Better_DAO_User_PlaceLog::getInstance($this->uid);
		
		$poiId || $poiId = $this->lastPoiId;
		$row = $dao->getMyCheckinCount($poiId);
		
		return $row['checkin_time'];	
	}
	
	/**
	 * 用户Checkin历史
	 * 
	 * @param $poiId
	 * @return array
	 */
	public function history($page=1, $count=BETTER_PAGE_SIZE, $poiId=0)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'rts' => array(),
			);
			
		$params = array(
			'page' => $page,
			'count' => $count,
			'poi' => $poiId,
			'uid' => $this->uid,
			);
			
		$sessUid = Better_Registry::get('sess')->get('uid');
		$userInfo = $this->user->getUserInfo();		
		$checkfollower = in_array($sessUid, $this->user->follow()->getFollowers($sessUid));		
		if ($sessUid==$this->uid || (($sessUid!=$this->uid && ($userInfo['priv']=='public' || ($userInfo['priv']=='protected' && $checkfollower ))) && $userInfo['priv']!='private')) {

			$return = $this->user->status()->getSomebody(array(
				'page' => $page,
				'page_size' => $count,
				'type' => 'checkin',
				'uid' => $this->uid,
				'without_me' => false,
				));
			foreach ($return['rows'] as $k=>$row) {
				$return['rows'][$k] = Better_Blog::parseBlogRow($row);
			}
		}
		
		return $return;
	}
	
	/**
	 * 
	 * 我常去的
	 * @param array $params
	 */
	public function &oftenCheckedPoisByCount(array $params)
	{
		$rows = array();
		$poiIds = Better_DAO_User_PlaceLog::getInstance($this->uid)->getOfterCheckedPoiIds($params);
		
		$return_rows = array();
		if (count($poiIds)>0) {
			$tmp = Better_DAO_Poi_Search::getInstance()->search(array(
				'page' => 1,
				'count' => count($poiIds),
				'poi_id' => array_keys($poiIds),
				'order' => 'force_distance',
				'lon' => (float)$params['lon'],
				'lat' => (float)$params['lat'],
				'range' => $params['range'],
				));
				
			foreach ($tmp['rows'] as $row) {
				list($lon, $lat) = Better_Functions::XY2LL($row['x'], $row['y']);
				$row['lon'] = $lon;
				$row['lat'] = $lat;
				$row['checkin_time'] = $poiIds[$row['poi_id']]['checkin_time'];
				$row['checkin_count'] = $poiIds[$row['poi_id']]['count'];
				
				$notification = array();
				if (defined('IN_API')) {
					$notification = Better_DAO_Poi_Notification::getInstance()->getLastest($row['poi_id']);
					$notification['id'] = $notification['nid'];
				}			
				$row['notification']= $notification;				
					
				$return_rows[$row['poi_id']] = $row;			
			}
		}
		
		return $return_rows;
	}
	
	/**
	 * 代码
	 * 
	 * @param unknown_type $page
	 * @param unknown_type $count
	 */
	public function fuckingCheckedPois($page=1, $count=BETTER_PAGE_SIZE)
	{
		$result = array(
			'rows' => array(),
			'total' => 0,
			);
		
		$tmp = Better_DAO_User_PlaceLog::getInstance($this->uid)->getCheckinedPois(1, BETTER_MAX_LIST_ITEMS);
		$result['total'] = $tmp['total'];
		if ($tmp['total']>0) {
			$pois = array();
			
			foreach ($tmp['rows'] as $row) {
				$_poiId = $row['poi_id'];
				$poi = Better_Poi_Info::getInstance($_poiId)->getBasic();
				$poi['checkin_time'] = $row['checkin_time'];
				$pois[$_poiId] = $poi;
				
			}
			foreach ($pois as $k=>$v) {
				if (!$v['name'] || $v['closed'] == 1) {
					unset($pois[$k]);
				}
			}
			
			$tmp = array_chunk($pois, 30);
			$pois = &$tmp[0];
			$result['rows'] = &$pois;
		}
		
		return $result;		
	}
	
	/**
	 * 用户Checkin过的poi（按照距离远近排序）
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function fuckingCheckinedPoisByDistance(array $params)
	{
		$result = array(
			'rows' => array(),
			'total' => 0,
			'pages' => 0,
			);
		
		$poiIds = Better_DAO_User_PlaceLog::getInstance($this->uid)->getCheckinedPoiIds();
		$result['total'] = count($poiIds);
		
		if ($result['total']>0) {
			$tmp = Better_DAO_Poi_Search::getInstance()->search(array(
				'lon' => $params['lon'],
				'lat' => $params['lat'],
				'page' => $params['page'],
				'count' => $params['count'],
				'range' => 99999999,//$params['range'],
				'order' => 'force_distance',
				'poi_id' => $poiIds
				));
			foreach ($tmp['rows'] as $row) {
				$row['poi_id'] = Better_Poi_Info::hashId($row['poi_id']);
			
				list($lon, $lat) = Better_Functions::XY2LL($row['x'], $row['y']);
				$row['lon'] = $lon;
				$row['lat'] = $lat;
				
				if ($row['logo']) {
					$row['logo_url'] = &$row['logo'];
				} else if ($row['category_image']) {
					$row['logo_url'] = Better_Poi_Category::getCategoryImage($row);
				} else {
					$row['logo_url'] = Better_Config::getAppConfig()->poi->default_logo;
				}
				
				if ($row['major']) {
					$row['major_detail'] = Better_User::getInstance($row['major'])->getUserInfo();
				}				
						
				$notification = array();
				if (defined('IN_API')) {
					$notification = Better_DAO_Poi_Notification::getInstance()->getLastest($row['poi_id']);
					
					if (isset($notification['nid'])) {
						$notification['id'] = $notification['nid'];
					}
				}		
				$row['notification'] = $notification;

				$result['rows'][] = $row;
			}

			$result['pages'] = Better_Functions::calPages($result['total'], $params['count']);
		}

		return $result;
	}
		
	/**
	 * 用户Checkin过的poi
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function checkinedPois($page=1, $count=BETTER_PAGE_SIZE)
	{
		$result = array(
			'rows' => array(),
			'total' => 0,
			);
		
		$tmp = Better_DAO_User_PlaceLog::getInstance($this->uid)->getCheckinedPois($page, $count);
		$result['total'] = $tmp['total'];
		
		if ($tmp['total']>0) {
			$poiIds = array();
			
			foreach ($tmp['rows'] as $row) {
				$pois[$row['poi_id']] = $row;
			}
			
			$rows = Better_DAO_Poi_Search::getInstance()->search(array(
				'page' => 1,
				'count' => BETTER_MAX_LIST_ITEMS,
				'poi_id' => array_keys($pois)
				));
			
			foreach ($rows['rows'] as $row) {
				$poi = $pois[$row['poi_id']];
				$row['checkin_time'] = $poi['checkin_time'];
				list($lon, $lat) = Better_Functions::XY2LL($row['x'], $row['y']);
				$row['lon'] = $lon;
				$row['lat'] = $lat;
				
				$pois[$row['poi_id']] = $row;
			}
			
			foreach ($pois as $k=>$v) {
				if (!$v['name']) {
					unset($pois[$k]);
				}
			}
			$result['rows'] = &$pois;
		}
		
		return $result;
	}
	
	/**
	 * 签到获得的分数
	 * 
	 * 只有获得的分数大于0，才能参与勋章/掌门的计算
	 * 
	 * @param array $params
	 * @return integer
	 */
	private function _checkinScore(array $params)
	{
		$this->getUserInfo();
		$score = 0;
		
		if (Better_Config::getAppConfig()->checkin_all_valid) {
			$score = 100;
			return $score;
		}
		
		$poiId = (int)$params['poi_id'];
		$distance = (float)$params['distance'];
		$checkins = (int)$params['checkins'];
		$lon = (float)$params['lon'];
		$lat = (float)$params['lat'];
		$lasttohere = (float)$params['lasttohere'];
		$now = time();
		$lastCheckin = (float)$this->userInfo['lbs_report'];
		$offset = $now - $lastCheckin;
		$poiDistance = $vKm = 0;
		$distanceOffset = 50000;
		
		if($offset){
			$speed = ($lasttohere/$offset)*3.6;
		}
		
		$checksucess = 0;
		if($lastCheckin==0){
			$checksucess = 1;
		} else if($offset<=1800 && $speed <500){
			$checksucess = 1;
		} else if($offset>=1800 && $speed <1000){
			$checksucess = 1;
		}		
		
		if(Better_Config::getAppConfig()->checkin->checkll){
			$checksucess && $distance<=$distanceOffset && $checksucess=1;
		}
		
		
		//易传媒特殊处理
		if($poiId == '17151558'){
			if(time()>= mktime(0, 30, 0, 4, 21, 2011) && time()<= mktime(7, 30, 0, 4, 22, 2011)){
				
				if($checksucess && $distance<=1000){
					$checksucess=1;
				}else{
					$checksucess = 0;
				}
				
			}
		}
		
		
		$lang = Better_Registry::get('lang');		
		if ($checkins<=Better_Config::getAppConfig()->checkin->everydaymax) {			
				$checksucess && $score = 100;					
		}
		Better_Log::getInstance()->logInfo("签到距离：".$distance.",是否有效签到：".$checksucess.",签到速度:".$speed."签到间隔：".$offset."签到坐标". $lon .$lat."签到结果".$score."签到50公里开关:".Better_Config::getAppConfig()->checkin->checkll."签到次数：".$checkins."签到数：".Better_Config::getAppConfig()->checkin->everydaymax,'checkintext');
		
		return $score;
	}
	
	
	/*
	 * 用户在POI签到的次数 
	 */
	public function checkinedPoisTimes($poiId)
	{		
		$tmp = Better_DAO_User_PlaceLog::getInstance($this->uid)->getMyCheckinCount($poiId);

		$result = $tmp;		
		return $result;
	}
	
	
	/**
	 * 取出多少天内checkin过的poi
	 * 
	 * @return array
	 */
	public function someDaysCheckinedPois($params){
		$uid = $params['uid']? $params['uid']: $this->uid;
		$days = isset($params['days'])? $params['days']: 0;
		$page = $params['page']? $params['page']: 1;
		$pagecount = $params['pagecount']? $params['pagecount']: 50;
		$reg_time = $params['reg_time'] ? $params['reg_time'] : 0;
		
		//parse regtime
		if($reg_time){
			$reg_time = date('Y-m-d', $reg_time);
			$y = substr($reg_time, 0, 4);
			$m = substr($reg_time, 5, 2);
			$d = substr($reg_time, 8, 2);
			$reg_time = gmmktime(23, 59, 59, $m, $d, $y);
		}
		
		$params_arr = array();
		$result = array();
		
		$params_arr = array(
			'page' => $page,
			'page_size' => $pagecount,
			'type' => 'checkin',
			'days'=> $days,
			'reg_time'=> $reg_time,
			'ignore_block' => true,
			'uid'=> $uid
		);
		
		$result = Better_DAO_User_Blog::getInstance($this->uid)->checkinedPois($params_arr);
		
		$tmp = array();
		$return =array('count'=>0, 'rows'=>array());
		if($result){
			foreach($result as $row){
				$poi = Better_Poi_Info::getInstance($row['poi_id'])->getBasic();
				if($poi['poi_id'] && $poi['closed']==0){
					$row['poi'] = $poi;
					$row['cluster_flag'] = 0;
					$tmp[] = $row;
				}
			}
		}
		
		if($tmp){
			$return['count'] = count($tmp);
			//$tmparr = array_chunk($tmp, $pagecount);
			//$return['rows'] = $tmparr[$page-1];
			$return['rows'] = $tmp;
		}
		
		return $return;
	}
	
	
	
	
	
	
}