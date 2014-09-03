<?php

/**
 * 
 * Poi资料
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Info extends Better_Poi_Base
{
	protected static $instance = array();
	protected $info = array();
	
	protected function __construct($poiId=null)
	{
		parent::__construct($poiId);
		if ($poiId) {
			if (is_array($poiId)) {
				$this->info = $poiId;	
				$this->poiId = $poiId['poi_id'];
			} 
			
			$this->_get();
		}
	}
	
	public static function getInstance($poiId, $renew=false)
	{
		if (defined('BETTER_AIBANG_POI') && BETTER_AIBANG_POI && !is_numeric($poiId)) {
			$poiId = Better_Service_Aibang_Pool::ab2our($poiId);	
		}
		
		if ($renew==true || !isset(self::$instance[$poiId])) {
			self::$instance[$poiId] = new self($poiId);
		}
		
		return self::$instance[$poiId];
	}	
	
	public static function createInstance($data)
	{
		$poiId = (int)$data['poi_id'];
		if (!isset(self::$instance[$poiId])) {
			if ($data['logo']) {
				$data['logo_url'] = &$data['logo'];
			} else {
				$data['logo_url'] = Better_Poi_Category::getCategoryImage($data);
			}
					
			self::$instance[$poiId] = new self($data);
		}
		
		return self::$instance[$poiId];
	}
	
	public static function destroyInstance($poiId)
	{
		if (isset(self::$instance[$poiId])) {
			unset(self::$instance[$poiId]);
		}
	}
		
	public function __get($key)
	{
		return isset($this->info[$key]) ? $this->info[$key] : '';
	}
	
	public function __set($key, $val)
	{
		$this->info[$key] = $val;
	}	
	
	/**
	 * 混淆poi的id
	 * 
	 * @param $str
	 * @return string
	 */
	public static function hashId($str)
	{
		$result = $str;
		
		if (defined('BETTER_HASH_POI_ID') && BETTER_HASH_POI_ID) {
			if (defined('IN_API')) {
				
			} else {
				$result = Better_Alpha::getInstance()->C($str);	
			}
		}
		
		return $result;
	}
	
	/**
	 * 解密poi的id
	 * 
	 * @param $str
	 * @return stirng
	 */
	public static function dehashId($str)
	{
		$result = $str;
		
		if (BETTER_HASH_POI_ID) {
			if (defined('IN_API')) {
			
			} else {
				$result = Better_Alpha::getInstance()->R($str);	
			}			
		}
		
		return $result;
	}
	
	/**
	 * 新建POI
	 * 
	 * @param $params
	 * @return array
	 */
	public static function create(array $params)
	{
		$result = array();
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1,
			'EMPTY_NAME' => -1,
			'INVALID_LL' => -2,
			'BAN_POINAME'=> -3,
			'BAN_POIADDRESS' => -4,
			'TOO_QUICK' => -5,
			'TOO_MORE' => -6,
			);
		$result['codes'] = &$codes;
		$code = $codes['FAILED'];
		$creator = $params['creator'];		
		$creator_createinfo =self::getUserNativedayCreateInfo($creator);
		$now = time();
		$lastCreate = (float)$creator_createinfo['last_time'];
		$offset = $now - $lastCreate;
		$hadCreate = (int)$creator_createinfo['t_count'];
		$adminuid = Better_Registry::get('sess')->get('admin_uid');		
		if($creator == BETTER_SYS_UID || $adminuid>0){
			$offset = 99999;
			$hadCreate = 1;			
		}
		Better_Log::getInstance()->logInfo(serialize($creator_createinfo)."**".$offset."**".$hadCreate."**是否为管理员：".$adminuid,'poi_creator');		
		if($offset<=Better_Config::getAppConfig()->poicreate->anti_spam->offset){
			$code = $codes['TOO_QUICK'];
		} else if ($hadCreate>=Better_Config::getAppConfig()->poicreate->everydaymax) {
			$code = $codes['TOO_MORE'];
		} else if ($params['name']=='') {
			$code = $codes['EMPTY_NAME'];
		} else if(Better_Filter::filterPoiwords($params['name'])){
			$code = $codes['BAN_POINAME'];
		} else if ($params['address'] && Better_Filter::filterPoiwords($params['address'])) {
			$code = $code['BAN_POIADDRESS'];
		} else {
			$x = $y = 0;
			$config = Better_Config::getAppConfig();
			$defaultLon = $config->location->default_lon;
			$defaultLat = $config->location->default_lat;
			
			$is_spam = false;
			
			if((isset($params['force_geo_coding']) && $params['force_geo_coding']===true) || !$params['lon'] || !$params['lat'] || $params['lon']<=0 || $params['lat']<=0){
				if ($params['address']) {
					
					$geoCodingResult = Better_Service_Google_Geocoding::request($params['address']);
	
					if (Better_LL::isValidLL($geoCodingResult['lon'], $geoCodingResult['lat'])) {
						$params['lon'] = $geoCodingResult['lon'];
						$params['lat'] = $geoCodingResult['lat'];					
					} else {
						$is_spam = true;
					}
					
					(!$params['country'] && $geoCodingResult['country']) && $params['country'] = $geoCodingResult['country'];
					(!$params['province'] && $geoCodingResult['province']) && $params['province'] = $geoCodingResult['province'];
					(!$params['city'] && $geoCodingResult['city']) && $params['city'] = $geoCodingResult['city'];
				} else {
					$is_spam = true;
				}
			}
			
			if ($is_spam) {
				$params['lon'] = $config->location->spam_lon;
				$params['lat'] = $config->location->spam_lat;
			}
			
			list($x, $y) = Better_Functions::LL2XY($params['lon'], $params['lat']);
			$certified = isset($params['certified']) ? ($params['certified'] ? 1 : 0) : 0;
			$categoryId = $params['category_id'] ? (int)$params['category_id'] : 4;
			$level = $params['level']? (int)$params['level'] : 99;
			$label = $params['label']? $params['label'] : '';
			$level_adjust = $params['level_adjust'] ? $params['level_adjust'] : 99;
			
			$poiToInsert = array(
				'name' => $params['name'],
				'category_id' => $categoryId,
				'x' => $x,
				'y' => $y,
				'address' => $params['address'],
				'phone' => $params['phone'],
				'country' => $params['country'],
				'province' => $params['province'],
				'city' => $params['city'],
				'creator' => $params['creator'],
				'certified' => $certified,	
				'level'=> $level,
				'level_adjust' => $level_adjust,
				'label'=> $label
				);
			
			$flag = Better_DAO_Poi_Info::getInstance()->insert($poiToInsert);
				
			if ($flag) {
				Better_Hook::factory(array(
					'Karma', 'Badge', 'Filter', 'Fulltext','Rp'
					))->invoke('PoiCreated', array(
						'poi_info' => $poiToInsert,
						'poi_id' => $flag,
						'uid' => $params['creator'],
						'doing' => 'new'
					));
					
				$result['message'] = Better_Hook::getMessages('PoiCreated');
				
				$result['poi_id'] = $flag;
				$code = $codes['SUCCESS'];
			} else {
				Better_Log::getInstance()->logAlert('poi create failed:['.$flag.']', 'poi');
			}
		}
		
		$result['code'] = $code;
		
		return $result;
	}
	
	public function &getBasic()
	{
		return $this->info;
	}
	
	/**
	 * 获取POI详情
	 * 
	 * @return array
	 */
	public function &get()
	{
		if ($this->info['creator']>0) {
			$this->info['creator_detail'] = Better_User::getInstance($this->info['creator'])->getUser();
		}
		
		if ($this->info['major']>0) {
			$this->info['major_detail'] = Better_User::getInstance($this->info['major'])->getUser();
		}
		
		return $this->info;
	}
	
	protected function _get()
	{
		if ($this->poiId) {
			$cacher = Better_Cache::remote();
			if (Better_Config::getAppConfig()->poi->cache_info) {
				$this->info = $cacher->get('kai_poi_'.$this->poiId);
			} else {
				$this->info = array();
			}			
			
			if (!is_array($this->info) || count($this->info)<=2) {
				if (!isset($this->info['name'])) {
					$this->info = Better_DAO_Poi_Info::getInstance()->getPoi($this->poiId);
				}
				
				list($lon, $lat) = Better_Functions::XY2LL($this->info['x'], $this->info['y']);
				$this->info['lon'] = $lon;
				$this->info['lat'] = $lat;
				
				if ($this->info['logo']) {
					$this->info['logo_url'] = &$this->info['logo'];
				} else {
					$this->info['logo_url'] = Better_Poi_Category::getCategoryImage($this->info);
				}
				
				if(preg_match('/^([0-9]+).([0-9]+)$/', $this->info['image_url']))	{
					$attach = Better_Attachment_Parse::getInstance($this->info['image_url'])->result();
					$this->info['attach_tiny'] = $attach['tiny'];
					$this->info['attach_thumb'] = $attach['thumb'];
					$this->info['attach_url'] = $attach['url'];	
				} else if (preg_match('/^http(.+)$/', $this->info['image_url'])) {
					$this->info['attach_tiny'] = $this->info['attach_thumb'] = $this->info['attach_url'] = $this->info['image_url'];
				}

				
				$__row = array();
				if(Better_Config::getAppConfig()->market->wlan->switch){		
					$wlanpoi = Better_Market_Cmcc::getInstance()->poilist();
					$poilist = array();
					foreach($wlanpoi as $row){
						foreach($row as $rows){
							$poilist[]= $rows;
						}	
					}
					if ( in_array($this->poiId, $poilist) ) {
						$is_event = true;
						$params['poi_id'] = $this->poiId;
						$datapolo = Better_DAO_Poi_Notificationpolo::getInstance()->getPoispecial($params);
						$datapolo = $datapolo['rows'][0];
						
						$datapolo['nid'] += 100000;

						$__row = array(
											'nid' => $datapolo['nid'],
											'id' => $datapolo['nid'],
											'poi_id' => $this->poiId,
											'title' => $datapolo['title'],
											'content' => $datapolo['content'],
											'dateline' => $datapolo['dateline'],
											'image_url' => $datapolo['image_url'],	
											'attach_url' => '',					
											'ownerid'=> $this->info['ownerid'],
											'name' => $this->info['name'],
											'action' => $this->info['action'],
											'sms_no' => $this->info['sms_no'],
											'sms_content' => $this->info['sms_content'],
											'url' => $this->info['url'],
											'phone' => $this->info['nphone']
										); 
					}
				}				
				
				$this->info['notification'] = $this->info['nid'] ? array(
					'nid' => $this->info['nid'],
					'id' => $this->info['nid'],
					'poi_id' => $this->poiId,
					'title' => $this->info['title'],
					'content' => $this->info['content'],
					'dateline' => $this->info['dateline'],
					'image_url' => $this->info['attach_thumb'],	
					'attach_url' => $this->info['attach_url'],					
					'ownerid'=> $this->info['ownerid'],
					'name' => $this->info['name'],
					'action' => $this->info['action'],
					'sms_no' => $this->info['sms_no'],
					'sms_content' => $this->info['sms_content'],
					'url' => $this->info['url'],
					'phone' => $this->info['nphone']
				) : $__row;
					
				$this->info['name'] = $this->info['closed']==0? $this->info['name'] : '(地球的某个角落)';
				$this->info['city'] = $this->info['closed']==0? $this->info['city'] : '';
				$this->info['address'] = $this->info['closed']==0? $this->info['address'] : '';				
				$cacher->set('kai_poi_'.$this->poiId, $this->info, 1800);
			}
			//Zend_Debug::dump($this->info);exit();
		}
	}
	
	/**
	 * 更新poi资料
	 * 
	 * @param $params
	 * @return bool
	 */
	public function update(array $params=array())
	{
		if (count($params)==0) {
			$params = $this->info;
			unset($params['poi_id']);
			unset($params['creator_detail']);
			unset($params['major_detail']);
			unset($params['notification']);
			unset($params['lon']);
			unset($params['lat']);
			unset($params['category_name']);
			unset($params['category_image']);
		}
		
		foreach ($params as $k=>$v) {
			if ($params[$k]!=$this->info[$k]) {
				$this->info[$k] = $params[$k];
			}
		}

		$flag = Better_DAO_Poi_Info::getInstance()->update($params, $this->info['poi_id']);
		
		if ($flag) {
			Better_Hook::factory(array(
				'Fulltext'
				))->invoke('PoiUpdated', array(
					'poi_info' => $params,
					'poi_id' => $this->info['poi_id']					
				));			
		}
		
		return $flag;
	}
	
	public function getsync()
	{
		if ($this->poiId){
			return Better_DAO_Poi_Info::getInstance()->getsync($this->poiId);
		}
	}
	public function getUserNativedayCreateInfo($uid){
		
		$result = Better_DAO_Poi_Info::getInstance()->getUserNativedayCreateInfo($uid);
		return $result;
	}
	/**
	 * @param array $params
	 */
	public function updatepoi($data)
	{	
		Better_Hook::factory(array(
				'Badge'
				))->invoke('PoiUpdated', array(
					'poi_info' => $params,
					'poi_id' => $this->info['poi_id'],
					'uid' => $data['uid'],
					'doing' => 'update'				
				));			
		$flag = Better_DAO_Poi_CheckUpdate::getInstance()->updatepoi($data);
		// update the POI in the table better_poi_check_update
		return $flag;
	}
}