<?php

/**
 * 使用Mysql搜索Poi
 * 
 * @package Better.Search.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Search_Poi_Mysql extends Better_Search_Poi_Base
{
	private $_top = array();
	
	public function __construct(array $params)
	{
		parent::__construct($params);
	}
	
	public function search()
	{
		$searchWithAiBangApi = false;

		if ($searchWithAiBangApi) {
			if ($this->params['keyword']) { //搜索请求带关键词时，请按相关度排序；
				$this->params['order'] = 'checkin';							
				$this->_searchPlusCheckin();				
			} else { //搜索请求不带关键词时，请按距离远近排序；		
				$this->_searchPlus();
			}
		} else {
			if ($this->params['page']==1 && $this->params['keyword']=='') {
				$this->mergeSpec($this->params);
			}
			
			$this->_search();
		}
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
	
	/**
	 * 处理关键词搜索  checkins 排序
	 * @return unknown_type
	 */
	protected function _searchPlusCheckin()
	{
		$searchParams = array();

		foreach ($this->params as $k=>$v) {
			$searchParams[$k] = isset($params[$k]) ? $params[$k] : $this->params[$k];
		}
		$searchParams['without_ab'] = true;	
		
		$ourPois = Better_DAO_Poi_Search::getInstance()->search($searchParams);	
		
		$abPois = Better_Service_Aibang_Poi::getInstance()->search($searchParams);

		$this->result['total'] = $ourPois['total'] + $abPois['total'];
	
		$this->result['count'] = $this->result['total'];
			
		
		foreach ($ourPois['rows'] as $key=>$row) {
			$tmpResult = $this->_makedata($row);
			if (count($tmpResult)>0) {
				$ourPois['rows'][$key] = $tmpResult;
				if (APPLICATION_ENV!='production') {
					$ourPois['rows'][$key]['address'] .= '__' . $row['checkins'];
				}
			} else {
				unset($ourPois['rows'][$key]);
			}
		}
		
		$page = (int)$searchParams['page'];
		$count = (int)$searchParams['count'];
		$begin = $page * $count - $count + 1;
		$end = $begin + $count - 1;	
	
		if ($end <= $ourPois['total']) {		
			$this->result['rows'] = $ourPois['rows'];				
		} else {
			$d = $end - $ourPois['total'];
			if ($d > 200) {
				$d = 200;
			}			
			$searchParams_ab = $searchParams;
			$searchParams_ab['page'] = ceil($d / $searchParams_ab['count']);							
			$abPois = Better_Service_Aibang_Poi::getInstance()->search($searchParams_ab);
						
			if ($abPois['rows']) {
				//$abPois['rows'] = $this->_delRow($abPois['rows']);
				
				foreach ($abPois['rows'] as $key => $row) {
					$tmpResult = $this->_makedata($row);
					if (count($tmpResult)>0) {
						$abPois['rows'][$key] = $tmpResult; 						
					} else {
						unset($abPois['rows'][$key]);
					}
				}
				
				$rows = array_merge($ourPois['rows'], $abPois['rows']);
				$this->result['rows'] = $rows;		
			} else {
				$this->result['rows'] = &$ourPois['rows'];
			}
			
		}
		return $this->result;		
	}
	
	/**
	 * 得到最后一个单元的id
	 */
	
	public function _getLastId($a)
	{
		$old_row = array_pop($a);
		$old_bizid = $old_row['bizid'];
		return $old_bizid;
	}
	
	/**
	 * 判断在不在我们的库里面
	 */
	public function _checkInOur($ab_id)
	{		
		$row = Better_DAO_Poi_Info::getInstance()->getPoiByAb($ab_id);
		if ($row) {
			return true;
		}
		return false;
	}
	
	
	/**
	 * 去除重复的数据
	 */
	public function _delRow($rows)
	{
		foreach ($rows as $k => $v) {
			if ($this->_checkInOur($v['aibang_id'])) {
				unset($rows[$k]);
				$this->result['total']--;
				$this->result['count']--;
			}
		}
		
		$temp_array = array();
		foreach ($rows as $v) {
			$temp_array[] = $v;
		}
		$rows = $temp_array;

		return $rows;
	}

	/**
	 * 判断搜索的范围是否在互联网大会区域里面
	 * 
	 */
	public function _checkPoint($lon, $lat, $poiInfo)
	{
		$lon1 = $poiInfo['lon'];
		$lat1 = $poiInfo['lat'];	
		$d = Better_Service_Lbs::getDistance($lon, $lat, $lon1, $lat1);
		
		if ($d < 50000) {
			return true;
		}
		
		return false;
	}

	
	/**
	 * 返回合并的搜索结果
	 */
	protected function _searchPlus(array $params=array())
	{
		$searchParams = array();

		foreach ($this->params as $k=>$v) {
			$searchParams[$k] = isset($params[$k]) ? $params[$k] : $this->params[$k];
		}
		$searchParams['without_ab'] = false;
		$searchParams_our = $searchParams;
		$searchParams_our['page'] = 1;	
		$ourPois = Better_DAO_Poi_Search::getInstance()->search($searchParams_our);
		$searchParams_ab = $searchParams;
		$searchParams_ab['page'] = 1;	
		$abPois = Better_Service_Aibang_Poi::getInstance()->search($searchParams_ab);
		$this->result['total'] = $ourPois['total'] + $abPois['total']; 
		$this->result['count'] = $this->result['total'];
		$abPois['rows'] = $this->_delRow($abPois['rows']);
		
		$page = (int)$searchParams['page'];
		$count = (int)$searchParams['count'];

		$begin = $page * $count - $count + 1;
		$end = $begin + $count - 1;

		if ($begin > $this->result['total']) {
			$this->result['rows'] =	array();	
			return $this->result;
		}
		
		$o = $a = 0;

		//$add_ids = array(876216, 876201);
		$add_ids = array();		
		foreach ($add_ids as $add_id) {
			$poiInfo = Better_Poi_Info::getInstance($add_id)->getBasic();
			if ( $this->_checkPoint($searchParams_our['lon'], $searchParams_our['lat'], $poiInfo) ) {
				$result[] = $this->_makedata($poiInfo);
				$this->result['count']++;
				$this->result['total']++;
			}		
		}
		
		$this->mergeSpec($searchParams_our);
		
		for ($i=0; $i<$end; $i++) {
			
			if (!$ourPois['rows'] && $ourPoisNone != true) {
				$searchParams_our['page']++; 
				$ourPois = Better_DAO_Poi_Search::getInstance()->search($searchParams_our);			
				$o = 0;
				
				if (!$ourPois['rows']) {
					$ourPoisNone = true;
				}
			} 
				
			if (!$abPois['rows'] && $abPoisNone != true) {				
				$searchParams_ab['page']++;
				$abPois = Better_Service_Aibang_Poi::getInstance()->search($searchParams_ab);
				$abPois['rows'] = $this->_delRow($abPois['rows']);		
				$a = 0;			
				
				if (!$abPois['rows']) {
					$abPoisNone = true;
				}
				
			}

			if ($abPois['rows'] && $ourPois['rows']) {
				$our_dist = $ourPois['rows'][$o]['distance'];
				$ab_dist = $abPois['rows'][$a]['dist'] * 1000;

				if ($our_dist <= $ab_dist) {
					$poi = $ourPois['rows'][$o];
					unset($ourPois['rows'][$o]);
					$o++;
				} else {
					$poi = $abPois['rows'][$a];
					unset($abPois['rows'][$a]);
					$a++;				
				}
			} elseif ($abPois['rows']) {
				$poi = $abPois['rows'][$a];
				unset($abPois['rows'][$a]);
				$a++;				
			} elseif ($ourPois['rows'])  {
								
				$poi = $ourPois['rows'][$o];			
				unset($ourPois['rows'][$o]);
				$o++;				
			} else {
				break;
			}

			if (in_array($poi['poi_id'], $add_ids)) {
				$this->result['count']--;
				$this->result['total']--;
				continue ;			
			}
	
			$result[] = $this->_makedata($poi);
			
		}
		//按照begin，end 取数据返回
		$r = array();
		foreach ($result as $key=>$row) {
			if ($key < $begin -1 ) {
				continue;
			}
			if ($key > $end - 1) {
				break;
			} 
			
			$r[] = $row;
		}
		
		$this->result['rows'] =	&$r;	
		return $this->result;
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
		
		$notification = array();
		if (defined('IN_API')) {
			$notification = Better_DAO_Poi_Notification::getInstance()->getLastest($row['poi_id']);
			if (isset($notification['nid'])) {
				$notification['id'] = $notification['nid'];
			}
		}		
		$row['notification'] = $notification;

		return $row;
	}
	
	/**
	 * 先取出5个我自建的地点
	 * 
	 * @return array
	 */
	protected function _top5MyCreated()
	{
		$uid = Better_Registry::get('sess')->get('uid');
		
		$result = Better_DAO_Poi_Search::getInstance()->search(array(
			'page' => 1,
			'page_limit' => 5,
			'certified' => 0,
			'creator' => $uid,
			'count' => 5,
			'order' => 'checkins'
			));

		return $result;
	}
	
	/**
	 * 搜索爱帮poi数据
	 * 
	 * @return array
	 */
	protected function _searchAb()
	{
		$rowsLimit = Better_Config::getAppConfig()->service->aibang->rows_limit;
		
		$this->params['page'] = (int)$this->params['page'];
		$this->params['page'] || $this->params['page'] = 1;
		$this->params['count'] = (int)$this->params['count'];
		$this->params['count'] || $this->params['count'] = BETTER_PAGE_SIZE;
		$this->params['count']>$rowsLimit && $this->params['count'] = $rowsLimit;
		
		$page = $this->params['page'];
		
		$ourResult = array(
			'total' => 0,
			'count' => 0,
			'rows' => array()
			);

		if ($page==1) {
			$ourCount = 5;
			$abCount = $this->params['count'] - $ourCount;
		} else {
			$ourCount = 0;
			$abCount = $this->params['count'];
		}

		if ($ourCount>0) {
			$this->_search(array(
				'page' => 1,
				'count' => $ourCount,
				'without_ab' => true,
				));
		} 

		$searchParams = array(
			'page' => $page,
			'count' => $abCount
			);
		foreach ($this->params as $k=>$v) {
			$searchParams[$k] = isset($searchParams[$k]) ? $searchParams[$k] : $this->params[$k];
		}		

		$abResult = Better_Service_Aibang_Poi::getInstance()->search($searchParams);
		
		$abRows = &$abResult['rows'];
		$abCount = &$abResult['count'];
		$abRealCount = &$abResult['real_count'];
		
		if ($abCount>0) {
			foreach ($abRows as $row) {
				$this->result['rows'][] = $row;
			}
			
			if ($abRealCount<($this->params['count']-$ourCount)) {
				$this->result['count'] = $abCount+$this->result['count'];
				$this->result['total'] = $this->result['count'];
			} else {
				$this->result['count'] = $this->result['total'] = Better_Config::getAppConfig()->service->aibang->rows_max;
			}
			
		} else {
			if ($page==1) {
				Better_Log::getInstance()->logInfo(serialize($searchParams), 'aibang');
				$this->_search($searchParams);
			}
		}

		return $this->result;
	}
	
	/**
	 * 
	 * 合并一些特殊poi
	 */
	public function mergeSpec(array $params)
	{
		$specIds = array();
		
		$specPois = Better_DAO_Poi_Top::getInstance()->getTop($params);
		foreach ($specPois as $specPoi) {
			$poiInfo = Better_Poi_Info::createInstance($specPoi)->getBasic();
			if ( $poiInfo['poi_id'] ) {
				$specIds[] = $poiInfo['poi_id'];
				
				$poiInfo['top'] = 1;
				$this->result['rows'][] = $this->_makedata($poiInfo);
				$this->result['count']++;
				$this->result['total']++;
			}				
		}
		
		$cacher = Better_Cache::remote();
		$cacher->set('poi_toped_'.md5(serialize($params)), 60);
	}	
	
	/**
	 * 搜索我们自己的poi数据库
	 * 
	 * @return array
	 */
	protected function _search(array $params=array())
	{
		$myTotal = 0;
		$myRows = array();
		
		$searchParams = array();
		foreach ($this->params as $k=>$v) {
			$searchParams[$k] = isset($params[$k]) ? $params[$k] : $this->params[$k];
		}
		if (isset($params['without_ab'])) {
			$searchParams['without_ab'] = $params['without_ab'];
		}

		$result = Better_DAO_Poi_Search::getInstance()->search($searchParams);

		$this->result['total'] = $myTotal + $result['total'];
		$this->result['count'] = $myTotal + $result['total'];
		
		$rows = array_merge($myRows, $result['rows']);
		foreach ($rows as $row) {
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
				$notification['id'] = $notification['nid'];
			}			
			$row['notification']= $notification;
			
			$this->result['rows'][] = $row;	
		}

		return $this->result;
	}

}