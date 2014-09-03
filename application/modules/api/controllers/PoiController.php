<?php

/**
 * POI相关api
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_PoiController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();

		$this->auth();
	}
	
	public function logcouponAction()
	{
		$this->xmlRoot = 'message';
		$secret = trim(urldecode($this->getRequest()->getParam('secret', '')));
		
		$secret = str_replace(' ', '', $secret);
		$secret = str_replace('>', '', $secret);
		$secret = str_replace('<', '', $secret);
		
		if ($secret) {
			$this->data[$this->xmlRoot] = 'ok';
			
			$key = $this->config->aes->key;
			$data = explode('|', trim(mcrypt_ecb(MCRYPT_RIJNDAEL_128, $key, Better_Functions::hex2bin($secret), MCRYPT_DECRYPT)));
			
			$poiId = (int)$data[0];
			$couponId = (int)$data[1];
			$no = trim($data[2]);
			
			Better_Log::getInstance()->logInfo('POI:['.$poiId.']COUPON:['.$couponId.']NO['.$no.']', 'couponlog', true);
		}
		
		$this->output();
	}
	
	/**
	 * 7.13 附近热门关键词
	 */
	public function hotwordsAction()
	{
		$this->xmlRoot = 'keywords';
		list($lon, $lat) = $this->mixLL();
		$range = (int)$this->getRequest()->getParam('range', 5000);
		
		$keywords = Better_Poi_Search_Log::getInstance()->nearbyKeywords(array(
			'lon' => $lon,
			'lat' => $lat,
			'range' => $range
			));
			
		foreach ($keywords as $keyword) {
			$this->data[$this->xmlRoot][] = array(
				'keyword' => $keyword
				);
		}
		
		$this->output();
	}
	
	/**
	 * 7.12 热门地点排序
	 */
	public function sortAction()
	{
		$category = trim($this->getRequest()->getParam('category', ''));
		list($lon, $lat) = $this->mixLL();
		
		if ($category=='normal' || $category=='tips' || $category=='checkin') {
			$rows = Better_Poi_Sort::sort(array(
				'order' => $category,
				'lon' => $lon,
				'lat' => $lat,
				));
				
			foreach ($rows as $row) {
				$this->data[$this->xmlRoot][] = array(
					'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
			
			$this->output();				
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.poi.invalid_sort_category');
		}
	}
	
	/**
	 * 举报poi
	 * 
	 * @return
	 */
	public function reportAction()
	{
		$this->xmlRoot = 'report';
		$id = (int)Better_Poi_Info::dehashId($this->getRequest()->getParam('id', 0));
		$reason = trim($this->getRequest()->getParam('reason', ''));
		$content = trim($this->getRequest()->getParam('content', ''));
		$enabled = ($this->getRequest()->getParam('enabled', 'false')=='true')?true:false;//用于判断是否支持修改，默认不支持
		
		if ($id>0) {
			$poi = Better_Poi_Info::getInstance($id);
			if ($poi->poi_id) {
				if( $reason =='incorrect' 
				   && !$this->user->isMuted()
				   && $this->user->getUserAvatar() != Better_Config::getAttachConfig()->global->avatar->default_url
				   &&$enabled
				   ){
					//用户有权限修改										
					$this->data[$this->xmlRoot] = array(
					'allowupdate'=>'true'
					);
				}else{							
						if (!Better_Poi_Report::getInstance($id)->reported($this->uid, $reason)) {
							$result = Better_Poi_Report::getInstance($id)->report(array(
								'reason' => $reason,
								'uid' => $this->uid,
								'content' => $content
								));
							
							switch ($result['code']) {
								case $result['codes']['INVALID_POI']:
									$this->errorDetail = __METHOD__.':'.__LINE__;
									$this->error('error.poi.invalid_poi');
									break;
								case $result['codes']['INVALID_REASON']:
									$this->errorDetail = __METHOD__.':'.__LINE__;
									$this->error('error.poi.invalid_reason');
									break;
								case $result['codes']['SUCCESS']:
									$poiBasic = $poi->getBasic();
									$message = ($reason =='incorrect' 
				  && ($this->user->getUserAvatar() == Better_Config::getAttachConfig()->global->avatar->default_url
				  && $enabled) )?$this->lang->poi->report->noavatar:$this->lang->poi->report->success;
									$this->data[$this->xmlRoot] = array(
										'message' => $message,
										'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
											'data' => &$poiBasic,
											'userInfo' => &$this->userInfo,
											)),
										);						
									break;
								case $result['codes']['FAILED']:
								default:
									$this->errorDetail = __METHOD__.':'.__LINE__;
									$this->serverError();
									break;
							}
						} else {
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->error('error.poi.duplicate_report');
						}
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
	 * 7.9修改地点基本属性（poi皇帝可用）
	 * 
	 * @return
	 */
	/*public function updateAction()
	{
		$this->xmlRoot = 'poi';
		
		if ($this->user->isMuted()) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.user.you_are_muted');
		}		
		
		$id = (int)$this->getRequest()->getParam('id', 0);
		$name = trim($this->post['name']);
		list($lon, $lat) = $this->mixLL();
		$category = (int)$this->getRequest()->getParam('category', 0);
		$country = trim($this->post['country']);
		$province = trim($this->post['province']);
		$city = trim($this->post['city']);
		$address = trim($this->post['address']);
		
		if ($id) {
			$poi = Better_Poi_Info::getInstance($id);
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
					
					$flag = $poi->update();
					
					if ($flag) {
						$poiDetail = $poi->get();
						
						$this->data[$this->xmlRoot] = $this->api->getTranslator('poi')->translate(array(
							'data' => &$poiDetail,
							'userInfo' => &$this->userInfo,
							));
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->serverError();
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.poi.update.wrong_rights');
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
	}*/
	
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
	
	/**
	 * 7.6 获取地点可选分类
	 * 
	 * @return
	 */
	public function classesAction()
	{

		$this->xmlRoot = 'classes';
		$cs = Better_Poi_Category::getAvailableCategories();
		
		foreach ($cs as $row) {
			$this->data[$this->xmlRoot][] = array(
				'class' => $this->api->getTranslator('poi_category')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)
				));
		}
		$this->output();
	}
	
	/**
	 * 7.5 地点搜索
	 * 
	 * @return
	 */
	public function searchAction()
	{
		$this->needLbsLog = true;
		$this->needSpecLog = true;
		
		list($lon, $lat, $range, $accuracy) = $this->mixLL();
		$range = $this->getRequest()->getParam('range', 50000);
		$query = trim($this->getRequest()->getParam('query', ''));
		$category = (int)$this->getRequest()->getParam('category', 0);
		$certified = (bool)($this->getRequest()->getParam('certified', 'false')=='false' ? false : true);
		$smallIcon = (bool)($this->getrequest()->getParam('small_icon', 'false')=='true' ? true : false);
		$ver = $this->getRequest()->getParam('ver', 1);
		$todo = (bool)($this->getrequest()->getParam('todo', 'false')=='true' ? true : false);
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
				if ($todo) {
					$poiIds = array();
					foreach ($rows['rows'] as $row) $poiIds[] = $row['poi_id'];
					$todos = Better_DAO_Todo::getInstance($this->uid)->getMaxBids($poiIds);
				} else {
					$todos = array();
				}

				//	记录poi搜索日志
				if ($this->page==1 && count($rows['rows'])>0) {
					$insertId = Better_Poi_Search_Log::getInstance()->log(array(
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
				
				foreach ($rows['rows'] as $row) {
					if ($todo && key_exists($row['poi_id'], $todos)) $row['todo'] = $todos[$row['poi_id']];
					$this->data[$this->xmlRoot]['pois'][] = array(
						'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
							'small_icon' => $smallIcon,
							'logid' => $insertId
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
							'small_icon' => $smallIcon
							)),
						);
				}
				break;
		}
		
		$this->output();

	}
	
	/**
	 * 贴士
	 * 
	 * @return
	 */
	public function tipsAction()
	{
		if ($this->user->isMuted()) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.user.you_are_muted');
		}

		switch ($this->todo) {
			case 'update':
				$this->_tipsUpdate();
				break;
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.request.not_found');
				break;
		}
		$this->output();
	}
	
	/**
	 * 7.3 用户报道
	 * 
	 * @return
	 */
	public function checkinAction()
	{
		define('IN_CHECKIN', true);
		
		$this->needLbsLog = true;
		$this->xmlRoot = 'checkin';
		
		$this->needPost();
		$userInfo = &$this->userInfo;;
		$uid = $userInfo['uid'];
		
		//$this->needSufficientKarma();
		
		if ($this->user->isMuted()) {
			$this->error('error.user.you_are_muted');
		}		
		
		$is_tips = $this->getRequest()->getParam('tips', '');
		$id = $this->getRequest()->getParam('id', '');
		$id = $this->__logClick($id);
		$id = Better_Poi_Info::dehashId($id);
		/*
		if (BETTER_4SQ_POI && $id && !is_numeric($id) && !strpos('-', $id)) {
			$id = Better_Service_4sq_Pool::foursq2our($id);
		}
		*/
		if (BETTER_AIBANG_POI && $id && !is_numeric($id)) {
			$id = Better_Service_Aibang_Pool::ab2our($id);
		}
		
		list($lon, $lat) = $this->mixLL();
		$text = trim($this->post['text']);
		$photo = (isset($_FILES) && isset($_FILES['photo'])) ? $_FILES['photo'] : null;
		$visibility = $this->getRequest()->getParam('visibility', 'all');
		$source = $this->getRequest()->getParam('source', 'kai');
		$ver = $this->getRequest()->getParam('ver', '1');
		$smallIcon = (bool)($this->getRequest()->getParam('small_icon', 'false')=='true' ? true : false);
		
		Better_Registry::set('_checkin_text_', $text);
		
		switch ($visibility) {
			case 'friend':
			case 'protected':
				$priv = 'protected';
				break;
			case 'private':
				$priv = 'private';
				break;
			default:
				$priv = 'public';
				break;
		}
		
		if ($id>0) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$poi = Better_Poi_Info::getInstance($id);
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
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
				
				Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				
				$result = Better_User::getInstance($uid)->checkin()->checkin(array(
					'lon' => $lon,
					'lat' => $lat,
					'poi_id' => $id,
					'priv' => $priv,
					'source' => $source,
					'message' => $text,
					'attach' => $photo,
					'checkin_need_sync' => ($this->getRequest()->getParam('sync', 'true')=='false' ? 0 : 1),
					'need_sync' => ($this->getRequest()->getParam('sync', 'true')=='false' ? 0 : 1),
					'is_tips' => $is_tips,
					));
				Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				switch ($result['code']) {
					case $result['codes']['POST_SAME_CONTENT']:
						Better_Controller::sendSquidHeaderC($result['code']);
						$this->error('error.statuses.post_same_content');
						break;
					case $result['codes']['DUPLICATED_CHECKIN']:
						Better_Controller::sendSquidHeaderC($result['code']);
						$this->error('error.statuses.duplicated_checkin');
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
						
						switch ($ver) {
							/*==============================================
							 * 
							 * 版本2
							 * 
							 ==============================================*/							
							case '2':
								//	解析签到后数据
								Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
								$archievments = $this->user->achievement()->apiParse();
								Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
								
								$visitInfo = $this->user->checkin()->parseLastCheckin();
								$visitInfo['this_score'] = $result['score'];
								Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
								
								$poi = Better_Poi_Info::getInstance($id, true);
								$poiInfo = $poi->get();
								$majorInfo = &$poiInfo['major_detail']; 
								Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
								
								$this->data[$this->xmlRoot] = array(
									'message' => $message,
									'poi' => $this->api->getTranslator('poi')->translate(array(
										'data' => $poi->get(),
										'userInfo' => &$this->userInfo,
										'small_icon' => $smallIcon
										)),
									'achievements' => $this->api->getTranslator('archievements')->translate(array(
										'userInfo' => &$this->userInfo,
										'data' => &$archievments
										)),
									'visit_info' => $this->api->getTranslator('poi_visit_info')->translate(array(
										'data' => &$visitInfo,
										'userInfo' => &$this->userInfo
										)),
									'mayor_info' => $this->api->getTranslator('poi_visit_major')->translate(array(
										'poi_id' => $poiInfo['poi_id'],
										'data' => $majorInfo,
										'userInfo' => &$this->userInfo
										)),
									'tips' => array(),
									'specials' => array()
									);		
								
								//	本地贴士
								Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
								$rows = Better_Poi_Tips::getRangedTips(array(
									'poi_id' => $poi->poi_id,
									'page' => 1,
									'count' => 1,
									'order' => 'poll',
									));
								$tips = &$rows['rows'];
								Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
								
								$_tid = 0;
								$_tdata = array();
								foreach ($tips as $row) {
									$_tid = $row['bid'];
									$_tdata = $row;
									$this->data[$this->xmlRoot]['tips'][] = array(
										'status' => $this->api->getTranslator('status')->translate(array(
											'data' => &$row,
											'userInfo' => &$this->userInfo
											)),
										);
								}
								Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);					

								//	本地优惠
								$platform = '0';
								$_array_temp = $this->user->cache()->get('client');
								if ( in_array( $_array_temp['platform'], array(1, 'S60', 's60') ) ) {
									$platform = '1';
								}								
								
								$rows = Better_Poi_Notification::getInstance($poi->poi_id)->getAll(1, 2, $platform);
								Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
								foreach ($rows['rows'] as $row) {
									$this->data[$this->xmlRoot]['specials'][] = array(
										'special' => $this->api->getTranslator('special')->translate(array(
											'data' => array(
												'category' => 'coupon',
												'message' => $row['content'],
												'url' => $row['id'],
												'image_url' => $row['image_url'],
												),
											)),
										);
								}

								if (APPLICATION_ENV!='production') {
									if ($_tid) {
										$this->data[$this->xmlRoot]['specials'][] = array(
											'special' => $this->api->getTranslator('special')->translate(array(
												'data' => array(
													'category' => 'tips',
													'message' => $_tdata['message'],
													'image_url' => $_tdata['attach_thumb_url'],
													'url' => $_tid
													),
												)),
											);
									}
									
									$this->data[$this->xmlRoot]['specials'][] = array(
										'special' => $this->api->getTranslator('special')->translate(array(
											'data' => array(
												'category' => 'poi',
												'message' => 'poi_test_message',
												'image_url' => 'http://k.ai/images/poi/category/101/life.png',
												'url' => 9000
												),
											)),
										);
								}
								$_uid = BETTER_SYS_UID;
								$_userInfo = Better_User::getInstance($_uid)->getUserInfo();
								// 4.8  增加签到后提示1小时内附近(<=500m)的好友签到信息
								$fblog = $result['fblog'];
								//Better_Log::getInstance()->logInfo('$fblog: ' . print_r($fblog, true), 'status');
								if (count($fblog) > 0) {
									$_fuInfo = Better_User::getInstance($fblog['userid'])->getUserInfo();
									$this->data[$this->xmlRoot]['specials'][] = array(
										'special' => $this->api->getTranslator('special')->translate(array(
											'data' => array(
												'category' => 'user',
												'message' => $this->api->getTranslator('friendblog')->translate($result),
												'image_url' => $_fuInfo['avatar_small'],
												'url' => $fblog['userid'],
												),
											)),
										);
								} 
								Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
								break;
							/*==============================================
							 * 
							 * 默认输出
							 * 
							 ==============================================*/
							default:
								$this->data[$this->xmlRoot] = array(
									'message' => $message
									);								
								break;
						}

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
	
	/**
	 * 处理id
	 * @param $id
	 * @return unknown_type
	 */
	public function __logClick($id)
	{
		if (strpos($id, '_') === false) {
			return $id;
		} else {
			list($poi_id, $log_id, $no) = explode('_', $id);
			Better_DAO_Poi_Search_Logclick::getInstance()->insert(array(
																		'id' => $log_id,
																		'no' => $no,
																		'poi_id' => $poi_id,
																		)
																);
			return $poi_id;												
		}
	}
	
	/**
	 * 7.2 地点空间（私有）
	 * 
	 * @return
	 */
	public function showAction()
	{
		
		$platform = '0';
		$_array_temp = $this->user->cache()->get('client');
		if ( in_array( $_array_temp['platform'], array(1, 'S60', 's60') ) ) {
			$platform = '1';
		}		
		$userInfo = &$this->userInfo;
		
		$abId = trim($this->getRequest()->getParam('abid', ''));
		$id = $this->getRequest()->getParam('id', $abId);
		$id = $this->__logClick($id);
		$id = Better_Poi_Info::dehashId($id);
		
		$visitors_count = (bool)($this->getRequest()->getParam('visitors_count', 'true')=='true' ? true : false);
		$visitors = (bool)($this->getRequest()->getParam('visitors', 'false')=='true' ? true : false);
		$todors_count = (bool)($this->getRequest()->getParam('todors_count', 'false')=='true' ? true : false);
		$todors = (bool)($this->getRequest()->getParam('todors', 'false')=='true' ? true : false);
		$owners = (bool)($this->getRequest()->getParam('owners', 'true')=='true' ? true : false);
		$shout = (bool)($this->getRequest()->getParam('shout', 'false')=='true' ? true : false);
		$tip = (bool)($this->getRequest()->getParam('tip', 'false')=='true' ? true : false);
		$checkin = (bool)($this->getRequest()->getParam('checkin', 'false')=='true' ? true : false);
		$todo = (bool)($this->getRequest()->getParam('todo', 'false')=='true' ? true : false);
		$treasure = (bool)($this->getRequest()->getParam('treasure', 'false')=='true' ? true : false);
		$poiFlag = (bool)($this->getRequest()->getParam('poi', 'true')=='false' ? false : true);	
		$coupon = (bool)($this->getRequest()->getParam('coupon', 'false')=='true' ? true : false);
		$around = (bool)($this->getRequest()->getParam('around', 'false')=='true' ? true : false);
		$around_count = (int)$this->getRequest()->getParam('around_count', 1);
		$activity = (bool)($this->getRequest()->getParam('activity', 'false')=='true' ? true : false);
		
		$this->xmlRoot = 'poispace';
		$data = array(
			'poi'	=> array(),
			'visitors_count' => array(),
			'visitors' => array(),
			'todors_count' => array(),
			'todors' => array(),
			'owners' => array(),
			'statuses_normal' => array(),
			'statuses_tip' => array(),
			'statuses_checkin' => array(),
			'statuses_todo' => array(),
			'treasures' => array(),
			'coupons' => array()
			);
		/*	
		if (BETTER_4SQ_POI && $id && !is_numeric($id) && !strpos('-', $id)) {
			$id = Better_Service_4sq_Pool::foursq2our($id);
		}
		*/
		if (BETTER_AIBANG_POI && $id && !is_numeric($id)) {
			$id = Better_Service_Aibang_Pool::ab2our($id);
		}
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		$poi = Better_Poi_Info::getInstance($id);
		
		if ($poi->poi_id) {
			//	poi
			$poiDetail = $poi->get();
			if($poiDetail['closed']==1 && intval($poiDetail['ref_id'])>0) {
				$poi = Better_Poi_Info::getInstance($poiDetail['ref_id']);
				$poiDetail = $poi->get();
				$id = $poiDetail['ref_id'];
			}
			if (1 || $platform == '1') {
				$poiDetail['notification']['image_url'] = '';
				//$poiDetail['notification']['content'] = 'content';
			}
			$poiDetail['my_checkin_count'] = $this->user->checkin()->checkinsAtPoi($poi->poi_id);
			$poiDetail['last_checkin_at'] = $this->user->checkin()->lastCheckinTimeAtPoi($poi->poi_id);
			//$notification= Better_Poi_Notification::getInstance($poi->poi_id)->getPoispecial($specialdate);
			//$poiDetail['coupons_count'] = $rows['total'];
			$poiDetail['coupons_count'] = Better_Poi_Notification::getInstance($poi->poi_id)->getCheckedCount();
			$poiDetail['mayor_checkin_count'] = $poiDetail['major']>0 ? Better_DAO_User_PlaceLog::getInstance($poiDetail['major'])->getMyValidCheckinCount($poiDetail['poi_id']) : 0;
			$poiDetail['activity_count'] = Better_DAO_Activity::getInstance()->getActivitiesAtPoiCount($poi->poi_id); 
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			if ($poiDetail['closed']==0) {
				// activity
				if ($activity) {
					$activities = Better_DAO_Activity::getInstance()->getActivitiesAtPoi(array(
								'poi_id' => $poi->poi_id,
								'page' => 1,
								'count' => 1,
							));
					if (count($activities) > 0) {
						$activity = $activities[0];
						
						$data['activities'][] = array(
							'activity' => $this->api->getTranslator('activity')->translate(array(
								'data' => &$activity,
							)),
						);
						
						// 如果有活动了，暂时隐藏惊喜。
						// 过渡期的活动和惊喜是重复的。数据库里没有隐藏惊喜是考虑兼容老版本
						$coupon = false;
						$poiDetail['notification'] = null;
					}
				}
				
				if ($poiFlag==true) {
					$data['poi'] = $this->api->getTranslator('poi')->translate(array(
						'data' => &$poiDetail,
						'userInfo' => &$userInfo
						));
				}
				
				//	treasures
				if ($treasure==true) {
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					$rows = Better_Poi_Treasure::getInstance($poi->poi_id)->logs($this->page, $this->count);
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					
					if (is_array($rows['rows']) && $rows['count']>0) {
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
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				}

				// visitors_count, 访客统计
				if ($visitors_count == true) {
					$counters = Better_Poi_Checkin::getInstance($poi->poi_id)->count(array('uid' => $userInfo['uid']));
					$data['visitors_count']['me'] = $counters['me'];
					$data['visitors_count']['friend'] = $counters['friend'];
					$data['visitors_count']['other'] = $counters['other'];
					$data['visitors_count']['total'] = $counters['total'];
				}

				// visitors, 访客列表
				if ($visitors == true) {
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					$rows = Better_Poi_Checkin::getInstance($poi->poi_id)->users($this->page, $this->count, false, array('uid' => $userInfo['uid']));
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
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
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				}

				// todors_count, 想去人数统计
				if ($todors_count == true) {
					$counters = Better_Poi_Todo::getInstance($poi->poi_id)->count(array('uid' => $userInfo['uid']));
					$data['todors_count']['me'] = $counters['me'];
					$data['todors_count']['friend'] = $counters['friend'];
					$data['todors_count']['other'] = $counters['other'];
					$data['todors_count']['total'] = $counters['total'];
				}

				//	todors, 想去人列表, 排列顺序为: 我,好友,其他人
				if ($todors == true) {
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					$rows = Better_Poi_Todo::getInstance($poi->poi_id)->users($this->page, $this->count, false, array('uid' => $userInfo['uid']));
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					if ($rows['total']>0) {
						foreach ($rows['rows'] as $row) {
							$data['todors'][] = array(
								'todor' => $this->api->getTranslator('user_concise')->translate(array(
									'data' => &$row,
									'userInfo' => &$userInfo,
									)),
								);
						}
					}
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				}

				// owners
				if ($owners == true) {
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					$rows = Better_Poi_Owner::getInstance($poi->poi_id)->owners($this->page, $this->count);
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					if ($rows['total']>0) {
						foreach ($rows['rows'] as $row) {
							$data['owners'][] = array(
								'owner' => $this->api->getTranslator('user_concise')->translate(array(
									'data' => &$row,
									'userInfo' => &$userInfo,
									)),
								);
						}
					}
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				}

				// shout
				if ($shout==true) {
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					$rows = $this->user->status()->getSomePoi(array(
						'type' => 'normal',
						'poi' => $poi->poi_id,
						'page' => $this->page,
						'page_size' => $this->count
						));
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					
					foreach ($rows['rows'] as $row) {
						$data['statuses_normal'][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => $userInfo,
								)),
							);
					}
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				}
				
				// tip
				if ($tip==true) {
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					$rows = Better_Poi_Tips::getRangedTips(array(
						'poi_id' => $poi->poi_id,
						'page' => $this->page,
						'count' => $this->count,
						'order' => 'poll',
						));
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		
					foreach ($rows['rows'] as $row) {
						$data['statuses_tip'][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => $userInfo,
								)),
							);
					}
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				}
				
				// checkin
				if ($checkin==true) {
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					$rows = $this->user->status()->getSomePoi(array(
						'poi' => $poi->poi_id,
						'type' => 'checkin',
						'page' => $this->page,
						'page_size' => $this->count
						));
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
						
					foreach ($rows['rows'] as $row) {
						$data['statuses_checkin'][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => &$userInfo,
								)),
							);
					}
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				}

				// todo
				if ($todo == true) {
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					$rows = $this->user->status()->getSomeTodo(array(
						'poi' => $poi->poi_id,
						'type' => 'todo',
						'page' => 1,
						'page_size' => 1
						));
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
						
					foreach ($rows['rows'] as $row) {
						$data['statuses_todo'][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => &$userInfo,
								)),
							);
					}
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				}

				// coupon
				if ($coupon==true) {
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					$rows = Better_Poi_Notification::getInstance($poi->poi_id)->getAll($this->page, $this->count);
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
					foreach ($rows['rows'] as $row) {
						$row['address'] = $poiDetail['address'];
						$row['name'] = $poiDetail['name'];
$row['image_url'] = '';
						$data['coupons'][] = array(
							'coupon' => $this->api->getTranslator('coupon')->translate(array(
								'data' => &$row
								)),
							);
					}
					Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
				}
				
				//around关联数据
				if($around){
					$types = array(
						'tuangou'=>array('今日团购', '团购'), 
					);
			
					foreach($types as $what=>$conf){
						$tmp = array();
						
						$result = Better_DAO_Roundmore_Factory::create($what)->getAllMsg(array(
							'lon'=> $poiDetail['lon'],
							'lat'=> $poiDetail['lat'],
							'range'=> 50000,
							'page'=> 1,
							'count'=> $this->count,
							'poi_id'=> $id
						));
						
						
						if(count($result['rows'])>0){
							foreach($result['rows'] as $row){
								$tmp[] = array(
									'poiext'=>$this->api->getTranslator('around_common')->translate(array(
											'data' => &$row,
											'type' => $what,
											'label' => $conf[1]
											))
									);
							}
						}else{
							$tmp = array();
						}
						
						
						$data['around'][] = array(
								'item'=>array(
											'count'=>$result['total'],
											'title'=> $conf[0],
											'label'=> $conf[1],
											'type'=> $what,
											'poiexts'=>$tmp
							 			 ));
						
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
	
	/**
	 * 7.1 地点空间(公共)
	 * 
	 * @return
	 */
	public function publictimelineAction()
	{
		$this->needLbsLog = true;
		$this->needSpecLog = true;
		$this->xmlRoot = 'poispace_public';
		
		list($lon, $lat, $range, $accuracy) = $this->mixLL();
		$range = $this->getRequest()->getParam('range', 2000);
		$range==5000 && $range = 2000;
		$query = trim($this->getRequest()->getParam('query', ''));
		$poi = (bool)($this->getRequest()->getParam('poi', 'false')=='true' ? true : false);
		$tip = (bool)($this->getRequest()->getParam('tip', 'false')=='true' ? true : false);
		$coupon = (bool)($this->getRequest()->getParam('coupon', 'false')=='true' ? true : false);
		$checkined = (bool)($this->getRequest()->getParam('checkined', 'false')=='true' ? true : false);
		$favTips = (bool)($this->getRequest()->getParam('favorited_tips', 'false')=='true' ? true : false);
		$friendsTips = (bool)($this->getRequest()->getParam('friends_tips', 'false')=='true' ? true : false);
		$oftenCheckined = (bool)($this->getRequest()->getParam('often_checkined', 'false')=='true' ? true : false);
		$smallIcon = (bool)($this->getRequest()->getParam('small_icon', 'false')=='true' ? true : false);
		$polo = (bool)($this->getRequest()->getParam('polo', 'false')=='true' ? true : false);
		$around = (bool)($this->getRequest()->getParam('around', 'false')=='true' ? true : false);
		$todo = (bool)($this->getRequest()->getParam('todo', 'false')=='true' ? true : false);
		
		$page = $this->page;
		
		$clientCache = $this->user->cache()->get('client');
		if ($clientCache['platform']==8) {
			$cachedPage = (int)$this->user->cache()->get('pp_last_page');
			if (!$cachedPage || $page==1) {
				$this->user->cache()->set('pp_last_page', 1);
				$cachedPage = 1;
			}
			
			if ($page-$cachedPage>=1) {
				$page = $cachedPage+1;
				$this->user->cache()->set('pp_last_page', $page);
			}
		}

		$data = array(
			'place' => array(),
			'pois' => array(),
			'tips' => array(),
			'coupons' => array(),
			'favorited_tips' => array(),
			'checkined_pois' => array(),
			'friends_tips' => array(),
			'often_checkined_pois' => array()
			);
		
		$poiParams = array(
			'what' => 'poi',
			'page' => $page,
			'count' => $this->count,
			);
		$tipsParams = array(
			'what' => 'blog',
			'type' => 'tips',
			'page' => $page,
			'count' => $this->count,
			);
		$couponsParams = array(
			'page' => $page,
			'count' => $this->count,
			);
		
		if ($lon && $lat) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$geo = new Better_Service_Geoname();
			$geoInfo = $geo->getGeoName($lon, $lat);

			$address = '';
			if (trim($geoInfo['r1']) && trim($geoInfo['r2'])) {
				$address = str_replace('{NO1}', $geoInfo['r1'], $this->lang->poi->place->string);
				$address = str_replace('{NO2}', $geoInfo['r2'], $address);
			} else {
				$geoInfo = $geo->getAddress($lon, $lat);
				$address = $geoInfo['address'];
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
			$poiParams['level'] = $accuracy;
			
			$tipsParams['lon'] = $lon;
			$tipsParams['lat'] = $lat;
			$tipsParams['range'] = 5000;
			
			$couponsParams['lon'] = $lon;
			$couponsParams['lat'] = $lat;
			$couponsParams['range'] = 50000;
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		} 

		//	我常去的
		$_array_poi = array();
		if ($oftenCheckined==true) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$rows = $this->user->checkin()->oftenCheckedPoisByCount(array(
				'page' => 1,
				'count' => 2,
				'lon' => $lon,
				'lat' => $lat,
				'range' => $range
				));
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);				
			foreach ($rows as $row) {
				$_array_poi[] = $row['poi_id'];
				$data['often_checkined_pois'][] = array(
					'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						'small_icon' => $smallIcon
						)),
					);
			}
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		}
		
		//pois
		if ($poi==true) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			if ($this->ft()) {
				$poiParams['method'] = 'fulltext';
			}
			
			$poiParams['uid'] = $this->uid;
			$rows = Better_Search::factory($poiParams)->search();
			if ($todo) {
				$poiIds = array();
				foreach ($rows['rows'] as $row) $poiIds[] = $row['poi_id'];
				$todos = Better_DAO_Todo::getInstance($this->uid)->getMaxBids($poiIds);
			} else {
				$todos = array();
			}

			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			foreach ($rows['rows'] as $row) {
//				if (in_array($row['poi_id'], $_array_poi)) {
//					continue;
//				}
				if ($todo && key_exists($row['poi_id'], $todos)) $row['todo'] = $todos[$row['poi_id']];
				$data['pois'][] = array(
					'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						'small_icon' => $smallIcon
						)),
					);
			}
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		}		
		
			//	tips
		if ($tip==true) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$rows = Better_Poi_Tips::getRangedTips($tipsParams);
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			foreach ($rows['rows'] as $row) {
				$data['tips'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);						
		}			
		
		// coupons
		if ($coupon==true) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$rows = Better_Poi_Notification::search($couponsParams);
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			foreach ($rows['rows'] as $row) {
				if ($row['image_url']) {
					list($a, $b) = explode('.', $row['image_url']);
					if (is_numeric($a) && is_numeric($b)) {
						$attach = Better_Attachment_Parse::getInstance($row['image_url'])->result();
						$row['image_url'] = $attach['url'];
					}
				}
				
				$data['coupons'][] = array(
					'coupon' => $this->api->getTranslator('coupon')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		}					
		
		//	Checkined Pois
		if ($checkined==true) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$rows = $this->user->checkin()->fuckingCheckinedPoisByDistance(array(
				'page' => $this->page,
				'count' => $this->count,
				'lon' => $lon,
				'lat' => $lat,
				'range' => $range
				));
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);

			foreach ($rows['rows'] as $row) {
				$data['checkined_pois'][] = array(
					'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						'small_icon' => $smallIcon
						)),
					);
			}			
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		}
		
		//	Favorited Tips
		if ($favTips) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$rows = $this->user->favorites()->allTips($this->page, $this->count);
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			foreach ($rows['rows'] as $row) {
				$data['favorited_tips'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						))
					);
			}
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		}
		
		//	Friends Tips
		if ($friendsTips) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$rows = $this->user->blog()->friendsTips($this->page, $this->count, $lon, $lat, $range);
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			foreach ($rows['rows'] as $row) {
				$data['friends_tips'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						))
					);
			}
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);			
		}
		
		
		$this->data[$this->xmlRoot] = &$data;
		
		$this->output();
	}
		
	public function checkinsAction()
	{
		$userInfo = &$this->userInfo;
		$uid = $userInfo['uid'];
		
		$poiId = isset($this->post['poi_id']) ? $this->post['poi_id'] : ($this->getRequest()->getQuery('poi_id', '') ? $this->getRequest()->getQuery('poi_id', '') : $this->getRequest()->getParam('poi_id', ''));	
		$poiId = (int)Better_Poi_Info::dehashId($poiId);
		
		$this->xmlRoot = 'statuses';
		
		$poiId=='' && $poiId = $this->id;
		$username = trim($this->getRequest()->getParam('username', ''));
		$page = (int)$this->getRequest()->getParam('page', 1);
		$count = (int)$this->getRequest()->getParam('count', 20);
		
		$page<=0 && $page=1;
		$count = $count>50 ? 50 : $count;
		$count = $count<=0 ? 20 : $count;					
		
		if (!$poiId) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.poi.invalid_poi');
		} else {
			if ($username!='') {
				$user = Better_User::getInstance($username, 'username');
				if ($user->username==$username) {
					$tmp = Better_User_Checkin::getInstance($user->uid)->history($page, $count, $poiId);
					$result = &$tmp['rows'];
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.poi.invalid_username');
				}
			} else {
				$poi = Better_Poi_Info::getInstance($poiId);
				if ($poi->poi_id) {
					$result = Better_Poi_Checkin::getInstance($poiId)->all($page, $count);
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.poi.invalid_poi');
				}
			}
		}
		
		foreach ($result as $row) {
			$this->data[$this->xmlRoot][] = Better_Api::translateStatus($row, $userInfo);
		}		
		
		$this->output();
	}
	
	
	
	/**
	 * 附近探索列表
	 */
	public function aroundAction(){
		$this->xmlRoot = 'around';
		$data = array();
		
		list($lon, $lat, $range, $accuracy) = $this->mixLL();
		$range = $this->getRequest()->getParam('range', 5000);
		$type = $this->getRequest()->getParam('type', 'tuangou');
		$poi_id = $this->getRequest()->getParam('poi_id', 0);
		
		switch($type){
			case 'weibo':
				$conf = array('weibo', '微博', '附近微博');
				break;
			case 'tuangou':
			default:
				$conf = array('tuangou', '团购', '今日团购');
				break;
		}
		
		$result = Better_DAO_Roundmore_Factory::create($conf[0])->getAllMsg(array(
				'lon'=> $lon,
				'lat'=> $lat,
				'range'=> $range,
				'page'=> $this->page,
				'count'=> $this->count,
				'poi_id'=> $poi_id
		));
		
		if(count($result['rows'])>0){
			foreach($result['rows'] as $row){
				$data['poiexts'][] = array(
					'poiext'=> $this->api->getTranslator('around_common')->translate(array(
						'data' => &$row,
						'type'=> $conf[0],
						'label'=> $conf[1]
					))
				);
			}
		}else{
			$data['poiexts'] = array();
		}
		
		$data['count'] = $result['total'];
		$data['type'] = $conf[0];
		$data['title'] = $conf[2];
		$data['label'] = $conf[1];
		
		$this->data[$this->xmlRoot] = $data;
		
		$this->output();
		
	}
	
	

	/**
	 * 团购， 微博 详细信息
	 * type
	 */
	public function arounddetailAction(){
		$this->xmlRoot = 'around';
		$data = array();
		
		$id = $this->getRequest()->getParam('id', 0);
		$type = $this->getRequest()->getParam('type', 'tuangou');
		
		if($id){
			switch($type){
				case 'weibo':
					$conf = array('weibo', '微博');
					break;
				case 'tuangou':
				default:
					$conf = array('tuangou', '团购');			
					break;
			}
			
			$row = Better_DAO_Roundmore_Factory::create($conf[0])->get($id);
			if($row['id']){
				$data['poiext'] = $this->api->getTranslator('around_common')->translate(array(
					'data' => &$row,
					'type'=> $conf[0],
					'label'=> $conf[1]
				));	
			}else{
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.around.invalid_id');
			}	
			
		}else{
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.around.invalid_id');
		}
		
		
		$this->data[$this->xmlRoot] = $data;
		
		$this->output();
	}
	
	
	/**
	 * 优惠详情
	 */
	public function coupondetailAction(){
		$this->xmlRoot = 'coupon';
		$data = array();
		
		$id = $this->getRequest()->getParam('id', 0);
		
		if($id){
			if($id>100000){
				$id= $id-100000;
				$row = Better_Poi_Notification::getPoloCoupon($id);
				Better_Log::getInstance()->logInfo(serialize($row),'wlanimg');
			} else {			
				$row = Better_Poi_Notification::getCoupon($id);
			}	
			if($row['nid']){
				$data = $this->api->getTranslator('coupon')->translate(array(
							'data' => &$row
						));
			}else{
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.coupon.invalid_id');
			}
		}else{
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.coupon.invalid_id');
		}
		
		$this->data[$this->xmlRoot] = $data;
		
		$this->output();
	}
	
	
	private function _createCheckName()
	{
		$this->xmlRoot = 'result';
		$poiName = trim($this->getRequest()->getParam('poi_name', ''));
		
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
		$name = trim($this->post['name']);
		$phone = trim($this->getRequest()->getParam('phone', ''));
		$category = (int)$this->getRequest()->getParam('category', 0);
		$address = trim($this->post['address']);
		$city = trim($this->post['city']);
		$province = trim($this->post['province']);
		$country = trim($this->post['country']);
		$forceGeoCoding = false;
		
		$c = $this->user->cache()->get('client');
		if ($c['platform']!=8 && $c['platform']!=10 && $range>=5000) {
			//	如果范围大于5000，则使用Google GeoService
			// But，iPhone 和 Android 不使用 geocoding
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
			case $codes['TOO_QUICK']:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.too_quick');
				break;
			case $codes['TOO_MORE']:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.too_more');
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
	 * 7.4 发贴士
	 * 
	 * @return
	 */
	private function _tipsUpdate()
	{
		$this->xmlRoot = 'update';
		$this->needPost();
		$userInfo = &$this->userInfo;
		
		$id = $this->getRequest()->getParam('id', '');
		$id = $this->__logClick($id);
		$id = Better_Poi_Info::dehashId($id);
		
		$need_sync = $this->getRequest()->getParam('sync', 'true')=='false' ? 0 : 1;
		/*
		if (BETTER_4SQ_POI && $id && !is_numeric($id) && !strpos('-', $id)) {
			$id = Better_Service_4sq_Pool::foursq2our($id);
		}
		*/
		if (BETTER_AIBANG_POI && $id && !is_numeric($id)) {
			$id = Better_Service_Aibang_Pool::ab2our($id);
		}	
		$rid = (int)$this->getRequest()->getParam('in_reply_to_status_id', 0);
		$source = trim($this->post['source']);
		$text = trim($this->post['text']);

		if ($text=='' && !(is_array($_FILES) && isset($_FILES['photo']))) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.statuses.tips_required');
		}

		if ($id>0) {
			$poi = Better_Poi_Info::getInstance($id);
			if ($poi->poi_id) {
				//	上传图片
				$photo = '';
				if (is_array($_FILES) && isset($_FILES['photo'])) {
					if (defined('IN_API') && !preg_match('/gif/i', strtolower($_FILES['photo']['type']))) {
						$rotates = Better_Registry::get('image_rotates');
						if (isset($rotates[$this->name])) {
							$ih = Better_Image_Handler::factory($newFile);
							if ($ih) {
								$newFile = $ih->rotate($rotates[$this->name]);
							}
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
								'need_sync' => $need_sync,
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
	
/**
	 * 7.9修改地点基本属性（普通用户使用）
	 * 
	 * @return
	 */
	public function updateAction()
	{	
		$this->needPost();
		
		$this->xmlRoot = 'poi';		
		if ($this->user->isMuted()) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.user.you_are_muted');
		}		
		
		$id = (int)$this->getRequest()->getParam('id', 0);//position id
		//名称
		$name = trim($this->post['name']);
		//地址
		$address = trim($this->post['address']);
		//城市
		$city = trim($this->post['city']);
		//电话
		$phone = trim($this->post['phone']);
		// 分类
		$category = (int)$this->post['category'];		
		//经纬度
		$lon = (float)$this->post['lon'];
		$lat = (float)$this->post['lat'];
		
		if ($id) {
			$poi = Better_Poi_Info::getInstance($id); //old poi
			list($oldLon,$oldLat) = Better_Functions::XY2LL($poi->x, $poi->y);
			if ($poi->poi_id) {
					//把用户更改的内容填写到数据库中去
					$uid=$this->user->uid;
					$oldarr = array();
					$newarr = array();
					if($name && $poi->name != $name){
						$newarr['name'] = $name;
						$oldarr['name'] = $poi->name;
					}
					if($address && $poi->address != $address){
						$newarr['address'] = $address;
						$oldarr['address'] = $poi->address;
					}
					if($city && $poi->city != $city){
						$newarr['city'] = $city;
						$oldarr['city'] = $poi->city;
					}
					if($phone && $poi->phone != $phone){
						$newarr['phone'] = $phone;
						$oldarr['phone'] = $poi->phone;
					}
					if($category && $poi->category_id  != $category){
						$newarr['category'] = $category;
						$oldarr['category'] = $poi->category_id;
					}
					if($lon && $poi->lon != $lon){
						$newarr['lon'] = $lon;
						$oldarr['lon'] = $oldLon;
					}
					if($lat && $poi->lat != $lat){
						$newarr['lat'] = $lat;
						$oldarr['lat'] = $oldLat;
					}
					
					if($newarr || $oldarr){// user has update the position
						$uid = $this->user->uid;
						//组装$data
						//默认只可以改变名称，地址,  城市，电话，分类，和位置的经纬度
						$content = array('new'=>$newarr,
											  'old'=>$oldarr);
						$content = json_encode($content);
						$data = array('uid'=>$uid,
											'change_content'=>$content,
											'poi_id'=>$poi->poi_id,
											'checked'=>0,
											'dateline'=>time());
						$index = $poi->updatepoi($data);
						$this->data[$this->xmlRoot]['result'] = $index;
				    }else{
				    	$this->data[$this->xmlRoot]['result'] = 1;
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

	public function marktodoAction()
	{
		$this->needPost();
		$this->xmlRoot = 'update';
		$userInfo = &$this->userInfo;;
		$uid = $userInfo['uid'];
		$id = $this->getRequest()->getParam('id', '');
		$id = $this->__logClick($id);
		$id = Better_Poi_Info::dehashId($id);
		if (BETTER_AIBANG_POI && $id && !is_numeric($id)) {
			$id = Better_Service_Aibang_Pool::ab2our($id);
		}
		list($lon, $lat) = $this->mixLL();
		$visibility = $this->getRequest()->getParam('visibility', 'all');
		$text = trim($this->post['text']);
		$source = $this->getRequest()->getParam('source', 'kai');
		$sync = $this->getRequest()->getParam('sync', false);
		$site = $this->getRequest()->getParam('source', '');
		
		switch ($visibility) {
			case 'friend':
			case 'protected':
				$priv = 'protected';
				break;
			case 'private':
				$priv = 'private';
				break;
			default:
				$priv = 'public';
				break;
		}

		$poi = $id > 0 ? $poi = Better_Poi_Info::getInstance($id) : null;
		if (!$poi || !$poi->poi_id) {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.invalid_poi');	
		} else if ($poi->closed) {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.was_closed');	
		} else {
			$bid = Better_User::getInstance($uid)->blog()->add(array(
				'type' => 'todo',
				'message' => $text,
				'lon' => $lon,
				'lat' => $lat,
				'poi_id' => $id,
				'priv' => $priv,
				'source' => $source,
				'need_sync' => ($this->getRequest()->getParam('sync', 'true')=='false' ? 0 : 1),
			));
			if ($bid == -1) {
				Better_Controller::sendSquidHeaderC($bid);
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statues.post_need_check');
			} else if ($bid == -2 || $bid == -4) {
				Better_Controller::sendSquidHeaderC($bid);
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.user.you_are_muted');
			} else if ($bid == -5) {
				Better_Controller::sendSquidHeaderC($bid);
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.post_too_fast');			
			} else if ($bid == -6) {
				Better_Controller::sendSquidHeaderC($bid);
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.post_same_content');					
			} else if ($bid == -3) {
				Better_Controller::sendSquidHeaderC($bid);
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.post.ban_words_but');
			} else if($bid == -7) {
				Better_Controller::sendSquidHeaderC($bid);
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.statuses.post_not_allow_rt');	
			} else {
				Better_Controller::sendSquidHeaderC(1);
				$blog = Better_Blog::getBlog($bid);
				$blog['user']['user_lon'] = $blog['user']['lon'];
				$blog['user']['user_lat'] = $blog['user']['lat'];
				$message = $this->parseAchievements($this->langAll->global->this_todo);
				$this->data[$this->xmlRoot] = array(
					'message' => $message,
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => array_merge($blog['blog'], $blog['user']),
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		}
		$this->output();
	}

	public function dealtodoAction()
	{
		$this->needPost();
		$this->xmlRoot = 'message';
		$userInfo = &$this->userInfo;;
		$uid = $userInfo['uid'];
		$id = $this->getRequest()->getParam('id');
		$dealing = $this->getRequest()->getParam('dealing', '');
		if ($dealing == 'beenhere') { //已去过
			$result = Better_Blog::beentodo($id);
			if ($result) {
				$this->data[$this->xmlRoot] = $this->lang->todo->been_success;
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.todo.been_fail');	
			}
		} else if ($dealing == 'nottodo') {//不想去了
			$result = Better_Blog::canceltodo($id);
			if ($result) {
				$this->data[$this->xmlRoot] = $this->lang->todo->cancel_success;
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.todo.cancel_fail');	
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.todo.invalid_action');	
		}
		$this->output();
	}
}