<?php

/**
 * 
 * poi全文检索
 * 
 * @package Better.Search.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Search_Poi_Fulltext extends Better_Search_Poi_Base
{
	protected static $serverUrl = '';
	protected $log =  array();
	protected $dupSearched = false;
	protected $lastParams = array();
	
	public function __construct(array $params)
	{
		parent::__construct($params);
		
		self::$serverUrl = Better_Config::getAppConfig()->poi->fulltext->newserver;
		
		$this->resetResult();
	}
	
	protected function resetResult()
	{
		$this->result['total'] = 0;
		$this->result['rows'] = array();
		$this->result['count'] = 0;		
	}
	
	public function newSearch()
	{
		$this->resetResult();
		$this->_search();
		// 不足一页时追加4sq数据
		if ($this->result['total'] < $this->params['count']) {
		  $fsq_pois = $this->_search4sq();
		  if ($fsq_pois['count'] > 0) {
		    $this->result['total'] += $fsq_pois['total'];
		    $this->result['count'] += $fsq_pois['count'];
		    foreach ($fsq_pois['rows'] as $row) {
		      $this->result['rows'][] = $row;
		    }
		  }
		}
		return $this->result;
	}
	
	public function search()
	{
		return $this->newSearch();
	}
	
	public function _search()
	{
		//================================
		//	搜索参数初始化	START		//
		//================================
		$locFilter = true;
		$query = trim($this->params['keyword']);
		$range = $this->params['range'] ? (int)$this->params['range'] : 50000;
		$level = $this->params['level'] ? $this->params['level'] : '0.0000005';
		
		$this->params['range'] = $range;
		
		$page = (int)$this->params['page'];
		$page<=0 && $page = 1;
		
		$count = (int)$this->params['count'];
		$count<=0 && $count = BETTER_PAGE_SIZE;
		
		$direction = trim($this->params['direction']);
		$direction || $direction = 'dir_all';
		
		$lon = trim($this->params['lon']);
		$lon = $lon ? sprintf('%.5f', $lon) : '0';
		
		if (trim($this->params['lon_alpha'])) {
			$lonAlpha = trim($this->params['lon_alpha']);
		} else if ($range) {
			$lonAlpha = 1.2*($range/100000);
		} else {
			$lonAlpha = 0.01;
		}
		
		$lat = trim($this->params['lat']);
		$lat = $lat ? sprintf('%.5f', $lat) : '0';
		
		if (trim($this->params['lat_alpha'])) {
			$latAlpha = trim($this->params['lat_alpha']);
		} else if ($range) {
			$latAlpha = 1.2*($range/100000);
		} else {
			$latAlpha = 0.01;
		}
		
		$start = ($page - 1)*$count;
			
		$params = array(
			'wt' => 'json',
			'start' => $start,
			'rows' => $count,
			);
		if ($this->params['q']) {
			$params['q'] = $this->params['q'];
		} else {				
			if ($query == '') {
				$params['q'] = '*:* AND _val_:"recip(sum(product('.$level.',pow(level,2)),sum(pow(sub('.$lat.',lat),2),pow(sub('.$lon.',lon),2))),1,1,0)" AND ilike:1';
				$levelWeight = $level*5E-10;
				$distance = 'sum(pow(sub('.$lat.',lat),2),pow(sub('.$lon.',lon),2))';
				$sumScore = 'sum(product('.$levelWeight.',pow(level,2)),'.$distance.')';
				$finalScore = 'recip(max(sub('.$sumScore.',pow(radius,2)),1E-10),1,1,0)';
				$q = '*:* AND _val_:"'.$finalScore.'" AND ilike:1';
				$params['q'] = $q;
			} else {
				$latAdjust = 1 / cos($lat * 3.1415 / 180);
				
			 	if (preg_match('/^[A-Za-z]+$/', $query)) {
			 		$pinyinQuery = ' OR pinyin:('.$query.'*)^0.1';
			 	} else {
			 		$pinyinQuery = ' ';
			 	}
				
			 	$precision = $this->params['level'] ? $this->params['level'] : '1000';
				$pre2 = $precision * $precision * 1E-10;

				$distance = 'max(sub(sqrt(sum('.$pre2.',pow(product(sub('.$lat.',lat),'.$latAdjust.'),2),pow(sub('.$lon.',lon),2))), radius), 1E-5)';
				
				$q = '(more:('.$query.')'.$pinyinQuery.' OR name_chars:('.$query.'))^0 _val_:"product(map(query($qq),0.1,1E5,0.1),recip(product('.$distance.', level),1,1,0))"';
				$qq = '{!bedo}(name:('.$query.') OR label:('.$query.')^0.001 OR name_chars:('.$query.')^0.000001 OR more:('.$query.')^0.00000001'.$pinyinQuery.')';
				
				$params['q'] = $q;
				$params['qq'] = $qq;
				
			}	
		}
		Better_Log::getInstance()->logInfo($params['q'], 'fulltext', true);

		if( $locFilter && $lat && $lon && $range<1000000){
			$latDown = $latUp = $lat;
			$lonDown = $lonUp = $lon;
			$latDown =  $lat - $latAlpha;
			$latUp  =  $lat + $latAlpha;
			$lonDown =  $lon - $lonAlpha;
			$lonUp  =  $lon + $lonAlpha;
			
			$lonUp = sprintf('%.5f', $lonUp);
			$lonDown = sprintf('%.5f', $lonDown);
			$latUp = sprintf('%.5f', $latUp);
			$latDown = sprintf('%.5f', $latDown);
			
			$params['fq'] = '+lat:['.$latDown.' TO '.$latUp.']+lon:['.$lonDown.' TO '.$lonUp.']';	
			Better_Log::getInstance()->logInfo($params['fq'], 'fulltext', true);		
		}
		//================================
		//	搜索参数初始化	END			//
		//================================

		$sParams = $params;
		$sParams['lon'] = $lon;
		$sParams['lat'] = $lat;
		$sParams['keyword'] = $query;
		$sParams['range'] = $range;			
		$logsTotal = $this->mergeLogCount($sParams);
		
		$specIds = array();
		if ($page==1 && $query=='') {
			$specIds = $this->mergeSpec($sParams);	
		}
		$minDist = $maxDist = null;
		
		Better_Log::getInstance()->putData(array(
			'search_params' => $params
			), 'user_poi_trace');

		$newParams = array();
		$newParams['poi'] = '';
		$newParams['q'] = $query;
		$newParams['lat'] = $lat;
		$newParams['lon'] = $lon;
		$newParams['range'] = $range;
		$newParams['precision'] = $this->params['level'];
		$newParams['uid'] = $this->params['uid'];
		$newParams['wt'] = 'json';
		$newParams['rows'] = $count;
    $newParams['start'] = $start;
    if(isset($this->params['radius']) && is_numeric($this->params['radius']))
      $newParams['radius'] = intval($this->params['radius']);
			
		try {
			$_url = self::$serverUrl;
			$client = new Zend_Http_Client($_url, array(
				'keepalive' => false,
				'timeout' => 5
				));
				
			if ($this->dupSearched) {
				$tmp = array(
					'q' => $params['q']
					);
				$params = $tmp;
			}
			
			//$client->setParameterGet($params);
			$client->setParameterGet($newParams);
			$begin_time = microtime(true);
			$response = $client->request(Zend_Http_Client::GET);
			$end_time = microtime(true);
			$exec_time = $end_time - $begin_time;
			$_strlog = "Fulltext\t$exec_time\r\n";
			Better_Functions::sLog($_strlog, 'exec_time.log');
			Better_Registry::set('FULLTEXT', $exec_time);
			if ($response->getStatus() == 200) {
				$result = array();
				$result['q_geo'] = array(
					'lon' => $lon,
					'lat' => $lat,
					'location' => $locFilter,
					'lon_alpha' => $lonAlpha,
					'lat_alpha' => $latAlpha
					);
				$result['start'] = $start;
				
				$json = json_decode($response->getBody());
				$resultHead = $json->{'responseHeader'};
				$result['qtime'] = (int)$resultHead->{'QTime'}/1000;
				$resultDocs = $json->{'response'};
				$result['totalFound'] = $resultDocs->{'numFound'};
	
				$docs = $resultDocs->{'docs'};
				$result['docs'] = array();
				
				$cacher = Better_Cache::remote();
				$cs = $cacher->get('kai_poi_categories');
				
				$rows = array();
				
				
				$i = 1;
				foreach ($docs as $doc) {
					$poiId = (int)$doc->{'poi_id'};

					if (in_array($poiId, $specIds)) {
						continue;
					}
					
					$pLon = (float)$doc->{'lon'};
					$pLat = (float)$doc->{'lat'};
					
					list($pX, $pY) = Better_Functions::LL2XY($pLon, $pLat);
					
					$notification = array();
					if (defined('IN_API')) {
						$notification = Better_DAO_Poi_Notification::getInstance()->getLastest($poiId);
						$notification['id'] = $notification['nid'];
					}
					
					$distance = Better_Service_Lbs::getDistance((float)$lon, (float)$lat, $pLon, $pLat);
					
					if ($minDist==null || $distance<$minDist) {
						$minDist = $distance;
					}
					
					if ($maxDist==null || $distance>$maxDist) {
						$maxDist = $distance;
					}
					$categoryId = (int)$doc->{'category_id'};
					$categoryName = $cs[$categoryId]['category_name'];	
					$logo = Better_DAO_Poi_Info::getInstance()->getlogo($poiId);
					if(is_array($logo) && $logo['logo']){
						//$categoryImage = $logo['logo'];
						$categoryImage = 'cmcc.png';
					} else {					
						$categoryImage = $cs[$categoryId]['category_image'];
					}								
					
					$rows[] = array(
						'no' => $i,
						'poi_id' => $poiId,
						'cid' => $doc->{'cid'},
						'name' => $doc->{'name'},
						'phone' => $doc->{'phone'},
						'category_id' => $categoryId,
						'address' => $doc->{'address'},
						'lon' => $pLon,
						'lat' => $pLat,
						'x' => $pX,
						'y' => $pY,
						'level' => $doc->{'level'},
						'dist' => $distance,
						'country' => '',
						'city' => $doc->{'city'},
						'province' => '',
						'major' => $doc->{'major'},
						'notification' => $notification,
						'bonus' => $doc->{'bonus'},
						'major_detail' => $doc->{'major'} ? Better_User::getInstance($doc->{'major'})->getUserInfo() : array(),
						'users' => 0,
						'visitors' => 0,
						'category_name' => $categoryName,
						'category_image' => $categoryImage
						);
					$i++;	
				}
				$sParams['min_dist'] = $minDist;
				$sParams['max_dist'] = $maxDist;
				$this->mergeLog($sParams);
				$logsFound = count($this->log);
				
				foreach ($rows as $row) {
					$distance = $row['dist'];
					foreach ($this->log as $_poiId=>$_poiInfo) {
						if ($distance<=$_poiInfo['distance']) {
							$this->result['rows'][] = $_poiInfo;
							unset($this->log[$_poiId]);
						}
					}
					
					$this->result['rows'][] = $row;
				}
				
				foreach ($this->log as $_poiId=>$_poiInfo) {
					$this->result['rows'][] = $_poiInfo;
				}
				
				$this->result['total'] = $logsTotal + $this->result['total'] + $result['totalFound'];
				$this->result['pages'] = Better_Functions::calPages($this->result['total'], $count);
				$this->result['count'] = $logsFound + $this->result['count'] + count($this->result['rows']);
			}
		} catch (Exception $e) {
			error_log('FullText!!!');
			Better_Log::getInstance()->logInfo($e->getTraceAsString(), 'fulltext_exception');
		}	
		
		return $this->result;
	}
	
	
	/*
	 * 搜索关键词
	 */
	
	public function searchByKeyword($keyword)
	{
		//================================
		//	搜索参数初始化	START		//
		//================================
		$query = trim($keyword);
		$page = 1;
		$count = 9999;
		
		$direction = 'dir_all';
	
		$start = ($page - 1) * $count;
			
		$params = array(
			'wt' => 'json',
			'start' => $start,
			'rows' => $count,
			);

		$params['q'] = 'more:('.$query.')';
	

		//================================
		//	搜索参数初始化	END			//
		//================================

		$sParams = $params;
		//$sParams['lon'] = '';
		//$sParams['lat'] = '';
		$sParams['keyword'] = $query;
		//$sParams['range'] = '';			

		Better_Log::getInstance()->putData(array(
			'search_params' => $params
			), 'user_poi_trace_keyword');
				
		$client = new Zend_Http_Client(self::$serverUrl, array(
			'keepalive' => false
			));
		
		$client->setParameterGet($params);
		
		$begin_time = microtime(true);
		$response = $client->request(Zend_Http_Client::GET);
		$end_time = microtime(true);
		$exec_time = $end_time - $begin_time;
		$_strlog = "Fulltext_keyword\t$exec_time\r\n";
		Better_Functions::sLog($_strlog, 'exec_time.log');
					
		if ($response->getStatus() == 200) {
			$result = array();
			$result['start'] = $start;
			
			$json = json_decode($response->getBody());
			$resultHead = $json->{'responseHeader'};
			$result['qtime'] = (int)$resultHead->{'QTime'}/1000;
			$resultDocs = $json->{'response'};
			$result['totalFound'] = $resultDocs->{'numFound'};

			$docs = $resultDocs->{'docs'};
			$result['docs'] = array();
			
			$cacher = Better_Cache::remote();
			$cs = $cacher->get('kai_poi_categories');
			
			$rows = array();
			
			foreach ($docs as $doc) {
				$poiId = (int)$doc->{'poi_id'}[0];
				$pLon = (float)$doc->{'lon'}[0];
				$pLat = (float)$doc->{'lat'}[0];
				
				list($pX, $pY) = Better_Functions::LL2XY($pLon, $pLat);

				
				$categoryId = (int)$doc->{'category_id'}[0];
				$categoryName = $cs[$categoryId]['category_name'];
				$categoryImage = $cs[$categoryId]['category_image'];
				
				$rows[] = array(
					'poi_id' => $poiId,
					);
			}
			
			
			foreach ($rows as $row) {
				$result['rows'][] = $row;
			}
			
		}
		return $result;
	}	
	
	/**
	 * 
	 * 合并一些特殊poi
	 */
	public function mergeSpec(array $params)
	{
		$poiIds = array();
		if (0 && APPLICATION_ENV!='production' && APPLICATION_ENV!='new_testing_main') {
			$specIds = array();
			$specIds[] = 9000;	//	测试用，苏大食堂

			foreach ($specIds as $specId) {
				$poiInfo = Better_Poi_Info::getInstance($specId)->getBasic();
				if ( $poiInfo['poi_id'] && $this->checkPoint((float)$params['lon'], (float)$params['lat'], $poiInfo) ) {
					$this->result['rows'][] = $this->_makedata($poiInfo);
					$this->result['count']++;
					$this->result['total']++;
				}		
			}				
		} else {
			$toped = Better_DAO_Poi_Top::getInstance()->getTop($params);
			foreach ($toped as $poi) {
				$poiInfo = Better_Poi_Info::createInstance($poi)->get();
				$poiIds[] = $poiInfo['poi_id'];
				$this->result['rows'][] = $poiInfo;
				$this->result['count']++;
				$this->result['total']++;
			}
		}
		
		return $poiIds;
	}
	

	/**
	 * 处理数组
	 */
	protected function _makedata($row)
	{
		$row['poi_id'] = Better_Poi_Info::hashId($row['poi_id']);
		
		list($lon, $lat) = Better_Functions::XY2LL($row['x'], $row['y']);
		$row['lon'] = $lon;
		$row['lat'] = $lat;
				
		if (!$row['poi_id']) {
			$aibang_id = $row['aibang_id'];
			$poiInfo = Better_Poi_Info::getInstance($aibang_id);		
			
			if ($poiInfo->closed || preg_match('/共产党/', $poiInfo->name)) {
				return array();
			}
			
			$row['checkins'] = $poiInfo->checkins;
			$row['users'] = $poiInfo->users;
			$row['tips'] = $poiInfo->tips;			
			$row['visitors'] = $poiInfo->visitors;
			$row['major'] = $poiInfo->major;
			$row['major_change_time'] = $poiInfo->major_change_time;
			$row['poi_id'] = $poiInfo->poi_id;
		}

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
		
		if ($row['dist']) {
			$row['distance'] = $row['dist'] * 1000;
		}		
		
		return $row;
	}	
	
	public function mergeLogCount(array $params)
	{
		return Better_DAO_Poi_Fulltext::getInstance()->joinInsertedCount($params);
	}
	
	/**
	 * 
	 * 合并新增的poi
	 */
	public function mergeLog(array $params)
	{
		$data = Better_DAO_Poi_Fulltext::getInstance()->joinInserted($params);
		
		foreach ($data['rows'] as $row) {
			$notification = array();
			if (defined('IN_API')) {
				$poiId = $row['poi_id'];
				$notification = Better_DAO_Poi_Notification::getInstance()->getLastest($poiId);
				$notification['id'] = $notification['nid'];
			}		

			list($row['lon'], $row['lat']) = Better_Functions::XY2LL($row['x'], $row['y']);
			$row['notification'] = $notification;
			
			$this->log[$row['poi_id']] = $row;
			//$this->result['rows'][] = $row;
		}
		//$this->result['total'] = (int)$this->result['total'] + count($data['rows']);
		//$this->result['count'] = (int)$this->result['count'] + count($data['rows']);
	}
	
	public function checkPoint($lon, $lat, &$poiInfo)
	{
		$lon1 = $poiInfo['lon'];
		$lat1 = $poiInfo['lat'];	
		$d = Better_Service_Lbs::getDistance($lon, $lat, $lon1, $lat1);
		
		if ($d < 50000) {
			return true;
		}
		
		return false;
	}	
}
