<?php

/**
 * POI相关api
 * 
 * @package 
 * @author 
 *
 */
class Public_PoiController extends Better_Controller_Public
{
	
	public function init()
	{
		parent::init();
		$this->xmlRoot = 'pois';
		$this->auth();
	}

	/**
	 * 7.5 地点搜索
	 * 
	 * @return
	 */
	public function searchAction()
	{
		$key = $this->getRequest()->getParam('key', '');
		$key = 'poi_' . $key;
		$this->validLimit($key, 100);
		
//		$ip = Better_Functions::getIP();
//		$key = 'poi_ip_' . $ip;
//		$this->validLimit($key, 100);
		
		$this->needLbsLog = true;
		list($lon, $lat, $range, $accuracy) = $this->mixLL();
		$range = $this->getRequest()->getParam('range', 50000);
		$query = trim(urldecode($this->getRequest()->getParam('query', '')));
		$category = (int)$this->getRequest()->getParam('category', 0);
		$certified = (bool)($this->getRequest()->getParam('certified', 'false')=='false' ? false : true);
		$ver = $this->getRequest()->getParam('ver', 1);

		switch ($ver) {
			case '2':
				$this->xmlRoot = 'poi_search';
				
				$this->data[$this->xmlRoot]['place'] = array();
				$this->data[$this->xmlRoot]['pois'] = array();
				
				$poiParams = array(
					'what' => 'poi',
					'lon' => $lon,
					'lat' => $lat,
					'range' => $range,
					'certified' => $certified,
					'keyword' => $query,
					'order' => 'distance',
					'category' => $category,
					'page' => $this->page,
					'count' => $this->count,
					'level' => $accuracy
					);				
				$poiParams['uid'] = $this->uid;	
				if ($lon && $lat) {
					$geo = new Better_Service_Geoname();
					$geoInfo = $geo->getGeoName($lon, $lat);
					
					$address = '';
					if (trim($geoInfo['r1']) && trim($geoInfo['r2'])) {
						$address = str_replace('{NO1}', $geoInfo['r1'], $this->lang->poi->place->string);
						$address = str_replace('{NO2}', $geoInfo['r2'], $address);
					}
		
					$this->data[$this->xmlRoot]['place'] = $this->api->getTranslator('place')->translate(array(
						'lon' => $lon,
						'lat' => $lat,
						'address' => $address,
						'city' => $geoInfo['name'],
						));			
						
					$poiParams['lon'] = $lon;
					$poiParams['lat'] = $lat;
					$poiParams['range'] = $range;
				} 				

				if ($this->ft()) {
					$poiParams['method'] = 'fulltext';
				}
				$rows = Better_Search::factory($poiParams)->search();
				if (count($rows['rows'])==0) {
					$newPoiParams = $poiParams;
					$newPoiParams['keyword'] = 'more:('.$query.')';
					$rows = Better_Search::factory($newPoiParams)->search();
				}
				
				foreach ($rows['rows'] as $row) {
					$this->data[$this->xmlRoot]['pois'][] = array(
						'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
							)),
						);
				}				
				break;
			default:
				$this->xmlRoot = 'pois';
		
				$rows = Better_Search::factory(array(
					'what' => 'poi',
					'lon' => $lon,
					'lat' => $lat,
					'range' => $range,
					'certified' => $certified,
					'keyword' => $query,
					'order' => 'distance',
					'category' => $category,
					'page' => $this->page,
					'count' => $this->count,
					'method' => $this->ft() ? 'fulltext' : 'mysql'
					))->search();
			
				foreach ($rows['rows'] as $row) {
					$this->data[$this->xmlRoot][] = array(
						'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
							)),
						);
				}
				break;
		}
		
		//	记录poi搜索日志
		if ($this->page==1 && count($rows['rows'])>0) {
			Better_Poi_Search_Log::getInstance()->log(array(
				'uid' => $this->uid,
				'lon' => $lon,
				'lat' => $lat,
				'keyword' => $query,
				'results' => (int)$rows['count'],
				'range' => $range
				));
		} else if ($this->page==1 && count($rows['rows'])==0 && APPLICATION_ENV=='production') {
			Better_Poi_Search_Log::getInstance()->logEmpty(array(
				'uid' => $this->uid,
				'lon' => $lon,
				'lat' => $lat,
				'keyword' => $query,
				'results' => (int)$rows['count'],
				'range' => $range
				));			
		}
		
		$this->output();
		

	}
	
	
	/**
	 * tips
	 * 
	 */
	public function tipsAction()
	{	
		$this->xmlRoot = 'tips';
		$page = $this->page;
		$count = $this->count;
		$poiId = $this->getRequest()->getParam('poi_id', 0);
		$poiId = (int)Better_Poi_Info::dehashId($poiId);
		if (!$poiId) $poiId = -1;
		
		$tips = Better_Poi_Tips::getInstance($poiId)->all($page, $count);

		foreach ($tips['rows'] as $key=>$v) {
			$data['uid'] = $v['uid'];
			$data['message'] = $v['message'];
			$data['nickname'] = $v['nickname'];
			$data['dateline'] = $v['dateline'];
			
			$this->data[$this->xmlRoot][$key]['tip'] = $data;
		}
						
		$this->output();
	}	
	
	/**
	 * 最新访客
	 */
	public function visitorAction()
	{
		$this->xmlRoot = 'users';
		$page = $this->page;
		$count = $this->count;
		$poiId = $this->getRequest()->getParam('poi_id', 0);	
		$poiId = (int)Better_Poi_Info::dehashId($poiId);

		$visitors = Better_Poi_Checkin::getInstance($poiId)->users($page, $count);
	
		foreach ($visitors['rows'] as $k=>$v) {	
			$data['uid'] = $v['uid'];
			$data['nickname'] = $v['nickname'];
		
			$this->data[$this->xmlRoot][$k]['user'] = $data;
		}
		
		$this->output();		
	}
	
	
	/**
	 * 获得POI的报到历史
	 */
	public function poicheckinsAction()
	{
		$poiId = Better_Poi_Info::dehashId($this->getRequest()->getParam('poi_id', ''));
		$page = $this->page;
		$count = $this->count;
		
		$this->xmlRoot = 'users';
			
		if ($poiId>0) {
			$userObj = $this->uid ? $this->user : Better_User::getInstance();
			$rows = $userObj->blog()->getAllBlogs(array(
				'type' => 'checkin',
				'poi' => $poiId,
				'page' => $page,
				), $count);
		}
		
		foreach ($rows['rows'] as $k=>$v){
			$data['uid'] = $v['uid'];
			$data['nickname'] = $v['nickname'];
			$data['dateline'] = $v['dateline'];
			
			$this->data[$this->xmlRoot][$k]['user'] = $data; 
		}	
	
		$this->output();
	}
	

	
	/**
	 * 7.2 地点空间（私有）
	 * 
	 * @return
	 */
	public function showAction()
	{
		$userInfo = $this->auth();
		
		$abId = trim($this->getRequest()->getParam('abid', ''));
		$id = Better_Poi_Info::dehashId($this->getRequest()->getParam('id', $abId));
		$visitors = (bool)($this->getRequest()->getParam('visitors', 'false')=='true' ? true : false);
		$shout = (bool)($this->getRequest()->getParam('shout', 'false')=='true' ? true : false);
		$tip = (bool)($this->getRequest()->getParam('tip', 'false')=='true' ? true : false);
		$checkin = (bool)($this->getRequest()->getParam('checkin', 'false')=='true' ? true : false);
		$treasure = (bool)($this->getRequest()->getParam('treasure', 'true')=='false' ? false : true);
		$poiFlag = (bool)($this->getRequest()->getParam('poi', 'true')=='false' ? false : true);	
		
		$this->xmlRoot = 'poispace';
		$data = array(
			'poi'	=> array(),
			'visitors' => array(),
			'statuses_normal' => array(),
			'statuses_tip' => array(),
			'statuses_checkin' => array(),
			'treasures' => array(),
			);
			
		if (BETTER_AIBANG_POI && $id && !is_numeric($id)) {
			$id = Better_Service_Aibang_Pool::ab2our($id);
		}

		$poi = Better_Poi_Info::getInstance($id);
		if ($poi->poi_id) {
			//	poi
			$poiDetail = $poi->get();
			if($poiDetail['closed']==1 && intval($poiDetail['ref_id'])>0) {
				$poi = Better_Poi_Info::getInstance($poiDetail['ref_id']);
				$poiDetail = $poi->get();
				$id = $poiDetail['ref_id'];
			}
			
			if ($poiDetail['closed']==0) {
				if ($poiFlag==true) {
					$data['poi'] = $this->api->getTranslator('poi')->translate(array(
						'data' => &$poiDetail,
						'userInfo' => &$userInfo
						));
				}
					
				//	treasures
				if ($treasure==true) {
					$rows = Better_Poi_Treasure::getInstance($poi->poi_id)->logs($this->page, $this->count);
					if ($rows['count']>0) {
						foreach ($rows['rows'] as $row) {
							if (isset($row['treasure_detail']['id'])) {
								$data['treasures'][] = array(
									'treasure' => $this->api->getTranslator('treasure')->translate(array(
										'data' => &$row['treasure_detail'],
										'userInfo' => &$this->userInfo
										)),
									);
							}
						}
					}
				}
	
				//	visitors
				if ($visitors==true) {
					$rows = Better_Poi_Checkin::getInstance($poi->poi_id)->users($this->page, $this->count);
					if ($rows['total']>0) {
						foreach ($rows['rows'] as $row) {
							$data['visitors'][] = array(
								'visitor' => $this->api->getTranslator('user_concise')->translate(array(
									'data' => &$row,
									'userInfo' => &$userInfo,
									)),
								);
						}
					}
				}
	
				// shout
				if ($shout==true) {
					$rows = $this->user->blog()->getAllBlogs(array(
						'type' => array('normal'),
						'poi' => (array)$poi->poi_id,
						'page' => $this->page,
						), $this->count);
					foreach ($rows['rows'] as $row) {
						$data['statuses_normal'][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => $userInfo,
								)),
							);
					}
				}
				
				// tip
				if ($tip==true) {
					$rows = Better_Poi_Tips::getRangedTips(array(
						'poi_id' => $poi->poi_id,
						'page' => $this->page,
						'count' => $this->count,
						'order' => 'poll'
						));
		
					foreach ($rows['rows'] as $row) {
						$data['statuses_tip'][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => $userInfo,
								)),
							);
					}				
				}
				
				// checkin
				if ($checkin==true) {
					$rows = $this->user->blog()->getAllBlogs(array(
						'type' => 'checkin',
						'poi' => $poi->poi_id,
						'page' => $this->page,
						'count' => $this->count
						));
						
					foreach ($rows['rows'] as $row) {
						$data['statuses_checkin'][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => &$userInfo,
								)),
							);
					}
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.was_closed');				
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.poi.invalid_poi');
		}
		
		$this->data[$this->xmlRoot] = &$data;
		
		$this->output();
	}
	
	public function checkinAction()
	{
		//define('IN_CHECKIN', true);
		

		$this->needPost();
		
		
		$this->needLbsLog = true;
		$this->xmlRoot = 'checkin';
		
		//$this->needPost();
		$userInfo = $this->auth();
		$uid = $userInfo['uid'];
		
		//$this->needSufficientKarma();
		
		if ($this->user->isMuted()) {
			$this->error('error.user.you_are_muted');
		}		
		
		$id = Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));
		if (BETTER_AIBANG_POI && $id && !is_numeric($id)) {
			$id = Better_Service_Aibang_Pool::ab2our($id);
		}
		
		list($lon, $lat) = $this->mixLL();
		$text = trim(urldecode($this->getRequest()->getParam('text', '')));
		$photo = (isset($_FILES) && isset($_FILES['photo'])) ? $_FILES['photo'] : null;
		$visibility = $this->getRequest()->getParam('visibility', 'all');
		//$source = $this->getRequest()->getParam('source', 'kai');
		$source = 'api';
		
		Better_Registry::set('_checkin_text_', $text);
		
		switch ($visibility) {
			case 'friend':
			case 'protected':
				//$priv = 'protected';
				$priv = 'public';
				break;
			case 'private':
				$priv = 'private';
				break;
			default:
				$priv = 'public';
				break;
		}
		
		if ($id>0) {
			$poi = Better_Poi_Info::getInstance($id);
			if ($poi->poi_id) {
				
				if (is_array($_FILES) && isset($_FILES['photo'])) {
					if ($this->post['image_rotate']) {
						$rotates = array(
							'photo' => $this->post['image_rotate']
							);
						Better_Registry::set('image_rotates', $rotates);
					}
										
					$at = Better_Attachment::getInstance('photo');
					$newFile = $at->uploadFile('photo');
		
					if (is_object($newFile) && ($newFile instanceof Better_Attachment)) {
						$result = $newFile->parseAttachment();
					} else {
						$result = &$newFile;
					}
		
					if (is_array($result) && $result['file_id']) {
						$photo = $result['file_id'];
					} else if (count(explode('.', $result))==2) {
						$photo = $result;
					} else {
						Better_Controller::sendSquidHeaderC($result);
						$this->error('error.status.upload.code_'.$result);
					}						
				}
				
				$result = Better_User::getInstance($uid)->checkin()->checkin(array(
					'lon' => $lon,
					'lat' => $lat,
					'poi_id' => $id,
					'priv' => $priv,
					'source' => $source,
					'message' => $text,
					'attach' => $photo,
					'checkin_need_sync' => ($this->getRequest()->getParam('sync', 'true')=='false' ? 0 : 1)
					));
					
				switch ($result['code']) {
					case $result['codes']['DUPLICATED_CHECKIN']:
						Better_Controller::sendSquidHeaderC($result['code']);
						$this->error('error.statues.duplicated_checkin');
						break;
					case $result['codes']['KARMA_TOO_LOW']:
						Better_Controller::sendSquidHeaderC($result['code']);
						$this->error('error.statuses.karma_too_low');
						break;
					case $result['codes']['TOO_FAST_CHECKIN']:
						Better_Controller::sendSquidHeaderC($result['code']);
						$this->error('error.statuses.too_fast_checkin');
						break;
					case $result['codes']['FORBIDDEN_WORDS']:
						Better_Controller::sendSquidHeaderC($result['code']);
						$this->error('error.statues.post_need_check');						
						break;
					case $result['codes']['SUCCESS']:
						$message = $this->parseAchievements($this->langAll->global->karma->source->checkin);
						
						if (trim($message)=='') {
							$message = $this->lang->checkin->success;
						}
						
						$this->data[$this->xmlRoot] = array(
							'message' => $message,
							);
						break;
					default:
						Better_Controller::sendSquidHeaderC(-30);
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.location.error');
						break;
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				Better_Controller::sendSquidHeaderC(-31);
				$this->error('error.checkin.invalid_poi');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			Better_Controller::sendSquidHeaderC(-32);
			$this->error('error.checkin.invalid_poi');
		}
		
		$this->output();
	}
	public function tipsupdateAction()
	{
		$this->xmlRoot = 'update';
		$this->needPost();
		$userInfo = $this->auth();
		
		$id = Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));
		if (BETTER_AIBANG_POI && $id && !is_numeric($id)) {
			$id = Better_Service_Aibang_Pool::ab2our($id);
		}
				
		$rid = (int)$this->getRequest()->getParam('in_reply_to_status_id', 0);
		$source = trim(urldecode($this->getRequest()->getParam('source', '')));
		$text = trim(urldecode($this->getRequest()->getParam('text', '')));

		if ($text=='') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.tips_required');
		}

		if ($id>0) {
			$poi = Better_Poi_Info::getInstance($id);
			if ($poi->poi_id) {
				//	上传图片
				$photo = '';
				if (is_array($_FILES) && isset($_FILES['photo'])) {
					if (defined('IN_API') && !preg_match('/gif/i', strtolower($_FILES['type']))) {
						$rotates = Better_Registry::get('image_rotates');
						if (isset($rotates[$this->name])) {
							$newFile = Better_Image_Handler::factory($newFile)->rotate($rotates[$this->name]);
						}
					}
										
					$at = Better_Attachment::getInstance('photo');
					$newFile = $at->uploadFile('photo');

					if (is_object($newFile) && ($newFile instanceof Better_Attachment)) {
						$result = $newFile->parseAttachment();
					} else {
						$result = &$newFile;
					}
		
					if (is_array($result) && $result['file_id']) {
						$photo = $result['file_id'];
					} else if (count(explode('.', $result))==2) {
						$photo = $result;
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.status.upload.code_'.$result);
					}
				}

				$in_reply_to_status_id = 0;
				$bid = Better_Blog::post($userInfo['uid'], array(
								'message' => $text,
								'upbid' => $in_reply_to_status_id,
								'attach' => $photo,
								'source' => $source,
								'poi_id' => $poi->poi_id,
								'type' => 'tips',
								));

				if ($bid==-1) {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.statues.post_need_check');
				} else if ($bid==-2 || $bid==-4) {
					Better_Controller::sendSquidHeaderC($bid);
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.user.you_are_muted');					
				} else if ($bid==-5) {
					Better_Controller::sendSquidHeaderC($bid);
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.statuses.post_too_fast');			
				} else if ($bid==-6) {
					Better_Controller::sendSquidHeaderC($bid);
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.statuses.post_same_content');								
				} else if ($bid==-3) {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.statuses.post.ban_words_but');
				} else if ($bid==0) {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->serverError();
				} else {
					$blog = Better_Blog::getBlog($bid);
					$blog['user']['user_lon'] = $blog['user']['lon'];
					$blog['user']['user_lat'] = $blog['user_lat'];
					
					$message = $this->parseAchievements($this->langAll->global->karma->source->tips);

					$this->data[$this->xmlRoot] = array(
						'message' => $message,
						'status' => $this->api->getTranslator('status')->translate(array(
							'data' => array_merge($blog['blog'], $blog['user']),
							'userInfo' => &$userInfo,
							)),
						);
				}

			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.invalid_poi');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.poi.invalid_poi');
		}
		
		$this->output();
	}
	
	public function publictimelineAction()
	{
		$this->needLbsLog = true;
		$this->xmlRoot = 'poispace_public';
		
		list($lon, $lat, $range) = $this->mixLL();
		$range = $this->getRequest()->getParam('range', 5000);
		$query = trim(urldecode($this->getRequest()->getParam('query', '')));
		$poi = (bool)($this->getRequest()->getParam('poi', 'false')=='true' ? true : false);
		$tip = (bool)($this->getRequest()->getParam('tip', 'false')=='true' ? true : false);
		$coupon = (bool)($this->getRequest()->getParam('coupon', 'false')=='true' ? true : false);
		$checkined = (bool)($this->getRequest()->getParam('checkined', 'false')=='true' ? true : false);
		$favTips = (bool)($this->getRequest()->getParam('favorited_tips', 'false')=='true' ? true : false);
		$friendsTips = (bool)($this->getRequest()->getParam('friends_tips', 'false')=='true' ? true : false);
		
		$data = array(
			'place' => array(),
			'pois' => array(),
			'tips' => array(),
			'coupons' => array(),
			'favorited_tips' => array(),
			'checkined_pois' => array(),
			'friends_tips' => array(),
			);
		
		$poiParams = array(
			'what' => 'poi',
			'page' => $this->page,
			'count' => $this->count,
			);
		$tipsParams = array(
			'what' => 'blog',
			'type' => 'tips',
			'page' => $this->page,
			'count' => $this->count,
			);
		$couponsParams = array(
			'page' => $this->page,
			'count' => $this->count,
			);
		
		if ($lon && $lat) {
			$geo = new Better_Service_Geoname();
			$geoInfo = $geo->getGeoName($lon, $lat);
			
			$address = '';
			if (trim($geoInfo['r1']) && trim($geoInfo['r2'])) {
				$address = str_replace('{NO1}', $geoInfo['r1'], $this->lang->poi->place->string);
				$address = str_replace('{NO2}', $geoInfo['r2'], $address);
			}

			$data['place'] = $this->api->getTranslator('place')->translate(array(
				'lon' => $lon,
				'lat' => $lat,
				'address' => $address,
				'city' => $geoInfo['name'],
				));			
				
			$poiParams['lon'] = $lon;
			$poiParams['lat'] = $lat;
			$poiParams['range'] = $range;
			
			$tipsParams['lon'] = $lon;
			$tipsParams['lat'] = $lat;
			$tipsParams['range'] = 5000;
			
			$couponsParams['lon'] = $lon;
			$couponsParams['lat'] = $lat;
			$couponsParams['range'] = 99999999;
		} 

		//pois
		if ($poi==true) {
			if ($this->ft()) {
				$poiParams['method'] = 'fulltext';
			}
			
			$rows = Better_Search::factory($poiParams)->search();
			foreach ($rows['rows'] as $row) {
				$data['pois'][] = array(
					'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		}		
		
			//	tips
		if ($tip==true) {
			//$rows = Better_Search::factory($tipsParams)->search();
			$rows = Better_Poi_Tips::getRangedTips($tipsParams);

			foreach ($rows['rows'] as $row) {
				$data['tips'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}						
		}			
		
		// coupons
		if ($coupon==true) {
			$rows = Better_Poi_Notification::search($couponsParams);
			foreach ($rows['rows'] as $row) {
				$data['coupons'][] = array(
					'coupon' => $this->api->getTranslator('coupon')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		}					
		
		//	Checkined Pois
		if ($checkined==true) {
			//$rows = $this->user->checkin()->checkinedPois($this->page, $this->count);
			$rows = $this->user->checkin()->fuckingCheckinedPoisByDistance(array(
				'page' => $this->page,
				'count' => $this->count,
				'lon' => $lon,
				'lat' => $lat,
				'range' => $range
				));

			foreach ($rows['rows'] as $row) {
				$data['checkined_pois'][] = array(
					'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}			
		}
		
		//	Favorited Tips
		if ($favTips) {
			$rows = $this->user->favorites()->allTips($this->page, $this->count);
			foreach ($rows['rows'] as $row) {
				$data['favorited_tips'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						))
					);
			}
		}
		
		//	Friends Tips
		if ($friendsTips) {
			$rows = $this->user->blog()->friendsTips($this->page, $this->count);
			foreach ($rows['rows'] as $row) {
				$data['friends_tips'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						))
					);
			}			
		}
		
		$this->data[$this->xmlRoot] = &$data;
		
		$this->output();
	}
	public function lltocityAction()
	{
		$this->needLbsLog = true;
		$this->xmlRoot = 'll_city';
		
		$lon = $this->getRequest()->getParam('lon', '');
		$lat = $this->getRequest()->getParam('lat', '');

		$geo = new Better_Service_Geoname();
		$geoInfo = $geo->getGeoName($lon, $lat);
		if(strlen($geoInfo['name'])<1){
			$this->error('error.poi.notfoundcity');
		} else {
			$address = '';
			if (trim($geoInfo['r1']) && trim($geoInfo['r2'])) {
					$address = str_replace('{NO1}', $geoInfo['r1'], $this->lang->poi->place->string);
					$address = str_replace('{NO2}', $geoInfo['r2'], $address);
			}
			
			$data['place'] = $this->api->getTranslator('place')->translate(array(
				'lon' => $lon,
				'lat' => $lat,
				'address' => $address,
				'city' => $geoInfo['name'],
				));		
			$this->data[$this->xmlRoot] = &$data;
		}
		$this->output();
	}
	
	
	
	/**
	 * 7.8 获取地点分类列表
	 * 
	 * @return
	 */
	public function categoriesAction()
	{
		$this->xmlRoot = 'categories';
		
		$langKey = $this->user->getUserLanguage();
		$cs = Better_Poi_Category::getAvailableCategories();
		
		foreach ($cs as $row) {
			$row['category_name'] = Better_Language::loadDbKey('category_name', $row, $langKey);
			$this->data[$this->xmlRoot][] = array(
				'category' => $this->api->getTranslator('poi_class')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)
				));
		}

		$this->output();
	}
	
	
	/**
	 * 7.7 新增POI
	 * 
	 * @return
	 */
	public function createAction()
	{

		switch ($this->todo) {
			//	校验poi名称
			case 'check_name':
			case 'checkname':
				$this->_createCheckName();
				break;
			default:
				$this->_createDo();
				break;
		}
		$this->output();
	}
	
	
	
	private function _createCheckName()
	{
		$this->xmlRoot = 'result';
		$poiName = trim(urldecode($this->getRequest()->getParam('poi_name', '')));
		
		if ($poiName!='') {
			$result = Better_Filter::filterPoiwords($poiName) ? 'false' : 'true';
		} else {
			$result = 'false';
		}
		
		$this->data[$this->xmlRoot] = $result;
		
		$this->output();
	}
	
	private function _createDo()
	{
		$this->xmlRoot = 'create_poi';
		
		if ($this->user->isMuted()) {
			$this->error('error.user.you_are_muted');
		}		
		
		list($lon, $lat, $range) = $this->mixLL();
		$name = trim(urldecode($this->getRequest()->getParam('name', '')));
		$phone = trim($this->getRequest()->getParam('phone', ''));
		$category = (int)$this->getRequest()->getParam('category', 0);
		$address = trim(urldecode($this->getRequest()->getParam('address', '')));
		$city = trim(urldecode($this->getRequest()->getParam('city', '')));
		$province = trim(urldecode($this->getRequest()->getParam('province', '')));
		$country = trim(urldecode($this->getRequest()->getParam('country', '')));
		$forceGeoCoding = false;
		
		if ($range>=5000 && $address!='') {
			//	如果范围大于30000，则使用Google GeoService
			$forceGeoCoding = true;	
		}
		
		$createParams = array(
			'name' => $name,
			'lon' => $lon,
			'lat' => $lat,
			'phone' => $phone,
			'category_id' => $category,
			'address' => $address,
			'country' => $country,
			'city' => $city,
			'province' => $province,
			'creator' => $this->uid,
			'force_geo_coding' => $forceGeoCoding
			);

		$result = Better_Poi_Info::create($createParams);
			
		$code = $result['code'];
		$codes = &$result['codes'];
		
		Better_Controller::sendSquidHeaderC($code);
		
		switch ($code) {
			case $codes['EMPTY_NAME']:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.invalid_name');
				break;
			case $codes['INVALID_LL']:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.wrong_data');
				break;
			case $codes['BAN_POINAME']:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.ban_poiname');
				break;
			case $codes['BAN_POIADDRESS']:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.ban_poiaddress');
				break;
			case $codes['SUCCESS']:
				$poi = Better_Poi_Info::getInstance($result['poi_id']);
				$poiDetail = $poi->get();
				
				$this->data[$this->xmlRoot] = array(
					'message' => $result['message'] ? $result['message'] : $this->lang->poi->create_ok,
					'poi' => $this->api->getTranslator('poi')->translate(array(
						'data' => &$poiDetail,
						'userInfo' => &$this->userInfo,
						)),
					);
				break;
			case $codes['FAILED']:
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->serverError();
				break;
		}
		
		$this->output();		
	}
	
	/**
	 * poi收藏
	 * 
	 * @return
	 */
	public function favoritesAction()
	{
		$this->needPost();
		
		switch ($this->todo) {
			case 'destroy':
				$this->_favoritesDestroy();
				break;
			case 'create':
				$this->_favoritesCreate();
				break;
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.request.not_found');
				break;
		}
		
		$this->output();
	}	
	/**
	 * 7.10 取消收藏
	 * 
	 * @return
	 */
	private function _favoritesDestroy()
	{
		$this->xmlRoot = 'poi';
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		$result = Better_User_PoiFavorites::getInstance($this->uid)->delete($id);
		
		switch ($result['code']) {
			case $result['codes']['SUCCESS']:
				$poiInfo = Better_Poi_Info::getInstance($id)->get();
				$poiInfo['favorited'] = false;
				
				$this->data[$this->xmlRoot] = $this->api->getTranslator('poi')->translate(array(
					'data' => &$poiInfo,
					'userInfo' => &$this->userInfo,
					));
				break;
			case $result['codes']['INVALID_POI']:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.invalid_poi');
				break;
			case $result['codes']['FAILED']:
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->serverError();
				break;
		}
	}		
		
	/**
	 * 7.9 收藏POI
	 * 
	 * @return
	 */
	private function _favoritesCreate()
	{
		$this->xmlRoot = 'poi';
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		$result = Better_User_PoiFavorites::getInstance($this->uid)->add($id);
		
		switch ($result['code']) {
			case $result['codes']['SUCCESS']:
				$poiInfo = Better_Poi_Info::getInstance($id)->get();
				$poiInfo['favorited'] = true;
				
				$this->data[$this->xmlRoot] = $this->api->getTranslator('poi')->translate(array(
					'data' => &$poiInfo,
					'userInfo' => &$this->userInfo,
					));
				break;
			case $result['codes']['INVALID_POI']:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.invalid_poi');
				break;
			case $result['codes']['FAILED']:
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.request.server_error');
				break;
		}
	}	
	
	/**
	 * poi投票
	 * 
	 * @return
	 */
	public function pollAction()
	{
		$this->needPost();
		
		if ($this->user->isMuted()) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.user.you_are_muted');
		}		
		
		switch ($this->todo) {
			case 'create':
				$this->_pollCreate();
				break;
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.request.not_found');
				break;			
		}
		
		$this->output();
	}

	/**
	 * 新建投票
	 * 
	 * @return
	 */
	private function _pollCreate()
	{
		$this->xmlRoot = 'status';
		$statusId = trim($this->getRequest()->getParam('status_id', ''));
		$option = $this->getRequest()->getParam('option', '');

		if ($option=='up' || $option=='down') {

			if (Better_Blog::validBid($statusId)) {
				$data = Better_Blog::getBlog($statusId);
				$blog = &$data['blog'];
				$starterUserInfo = &$data['user'];
				
				if ($blog['uid']==$this->uid) {
					$this->error('error.poi.poll.cant_poll_self');
				}

				if ($blog['type']=='tips') {
					$poll = Better_Poi_Poll::getInstance($blog['bid']);
					$result = $poll->poll(array(
						'uid' => $this->uid,
						'poll_type' => $option
						));
					$code = $result['code'];
					$codes = &$result['codes'];
					
					switch ($code) {
						case $codes['DUPLICATED']:
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->error('error.poi.poll.duplicated');
							break;
						case $codes['SUCCESS']:
							$data = Better_Blog::getBlog($statusId);
							$this->data[$this->xmlRoot] = $this->api->getTranslator('status')->translate(array(
								'data' => &$data['blog'],
								'userInfo' => &$this->userInfo
								));							
							break;
						default:
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->serverError();
							break;
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.poi.poll.invalid_status_id');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.poll.invalid_tip');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.poi.poll.invalid_option');
		}
	}	
}