<?php

/**
 * 用户经纬度解析
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Ll extends Better_User_Base
{
	protected static $instance = array();

	public static function getInstance($uid)
	{		
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}
	
	/**
	 * 解析某个用户的经纬度
	 * 
	 * @return array
	 */
	public function parse(array $params)
	{
		$result = array(
			'lon' => 0,
			'lat' => 0,
			'range' => 999999999
			);
		$resultUseCache = false;
		
		$lon = round((float)$params['lon'], 4);
		$lat = round((float)$params['lat'], 4);
		$lbs = trim($params['lbs']);
		$range = (int)$params['range'];
		$issetVer = isset($params['ver']);
		$ver = $params['ver'];
		$rawGps = (bool)$params['rawgps'];
		$age = isset($params['locage']) ? round((int)trim($params['locage']) / 1000) : '';
		$accuracy = isset($params['accuracy']) ? round(trim($params['accuracy'])) : '';
		//$ver!='0.2' && $ver = '0.1';
		if ($lbs=='') {
			$ver = '0.1';
		}
		
		$range || $range = 5000;
		$config = Better_Config::getAppConfig();
		$gotLon = -1;
		$gotLat = -1;
		
		if ($rawGps===true && $this->isValidLL($lon, $lat) && $ver=='0.1') {
			//	如果是原始gps数据，则加扰
			$tmp = Better_LL::parse($lon, $lat);
			$gotLon = $tmp['lon'];
			$gotLat = $tmp['lat'];
			
			//	针对iPhone特别处理
			if (isset($_POST['rawgps']) || isset($_POST['rowgps'])) {
				$c = $this->user->cache()->get('client');
				if ($c['platform']==8) {
					// 强制设定range为100，避免输出后被强制GeoCoding
					$range = 100;
				}
			}
		} else if ($rawGps===false && $this->isValidLL($lon, $lat) && ($issetVer || $ver=='0.1')) {
			//	如果不是原始gps数据，则保留其值
			$gotLon = $lon;
			$gotLat = $lat;		
			
			//	特别处理			
			if (isset($_POST['rawgps']) || isset($_POST['rowgps'])) {
				$range = 100;
			}
		} else {
			//	请求lbs定位
			$appLbs = Better_Service_Lbs::getInstance();
			$apiKey = $config->lbs->api_key;
			
			if ($ver=='0.2') {
				$add = " lat='$lat' lon='$lon' age='$age' accuracy='$accuracy'";
			} else {
				$add = '';
			}
			$xml = "<location ver='".$ver."' vendid='".$apiKey."' os='win' from='better' id='".$this->uid."'".$add."><locate hex='".$lbs."'></locate><ip>".Better_Functions::getIP()."</ip><urls html='1' wml='1'/></location>";
			$appLbs->getLL($xml, $this->uid, false);

			//	取出Cache中记录的定位结果，与lbs结果进行对比取舍
			$cached = Better_DAO_Lbs_Cache::getInstance()->get(array(
				'uid' => $this->uid
				));
			$cachedLon = trim($cached['lon']);
			$cachedLat = trim($cached['lat']);
			$cachedTime = $cached['time'];
			$cachedRange = trim($cached['range']);
			$lastCacheTime = $cached['cache_time'];
						
			if (!$appLbs->error && $this->isValidLL($appLbs->lon, $appLbs->lat)) {
				if ($this->isValidLL($cachedLon, $cachedLat)) {
					$thisLon = $appLbs->lon;
					$thisLat = $appLbs->lat;
					$thisTime = $appLbs->time;
					$thisRange = $appLbs->range;
					$nowTime = time();

					if ($thisRange<=5000) {
						//	range小于等于5km，认为lbs定位可信
						$gotLon = $thisLon;
						$gotLat = $thisLat;
						$range = $thisRange;
						
						$appLbs->cacheLastResult($this->uid);
					} else if ($thisRange>5000 && $thisRange<=10000) {
						//	range大于5km小于等于10km，采用第一种策略对缓存与lbs结果进行计算
						$deltaRange = $thisRange - $cachedRange;
						$deltaTime = $nowTime - $lastCacheTime;

						if ($deltaTime>=7200 || $deltaRange<=0) {
							$gotLon = $thisLon;
							$gotLat = $thisLat;
							$range = $thisRange;
							
							$appLbs->cacheLastResult($this->uid);
						} else {
							$ratio = $deltaTime/$deltaRange;
							$baseRatio = 7200/5000;

							if ($ratio>=$baseRatio) {
								$gotLon = $thisLon;
								$gotLat = $thisLat;
								$range = $thisRange;
								
								$appLbs->cacheLastResult($this->uid);
							} else {
								$gotLon = $cachedLon;
								$gotLat = $cachedLat;
								$range = $cachedRange;
								
								$resultUseCache = true;
							}
						}
						
					} else if ($thisRange>10000 && $thisRange<=30000) {
						//	range大于10km小于等于30km，采用第二种策略对缓存与lbs结果进行计算
						
						$deltaRange = $thisRange - $cachedRange;
						$deltaTime = $nowTime - $lastCacheTime;
						
						if ($deltaTime>=14400 || $deltaRange<=0) {
							$gotLon = $thisLon;
							$gotLat = $thisLat;
							$range = $thisRange;
							
							$appLbs->cacheLastResult($this->uid);
						} else {
							$ratio = $deltaTime/$deltaRange;
							$baseRatio = 8*3600/20000;
							
							if ($ratio>=$baseRatio) {
								$gotLon = $thisLon;
								$gotLat = $thisLat;
								$range = $thisRange;
								
								$appLbs->cacheLastResult($this->uid);
							} else {
								$gotLon = $cachedLon;
								$gotLat = $cachedLat;
								$range = $cachedRange;
								
								$resultUseCache = true;
							}
						}
													
					} else if ($thisRange>30000) {
						$deltaRange = $thisRange - $cachedRange;
						$deltaTime = $nowTime - $lastCacheTime;

						if ($deltaTime>=3600*12 || $deltaRange<=0) {
							$gotLon = $thisLon;
							$gotLat = $thisLat;
							$range = $thisRange;
							
							$appLbs->cacheLastResult($this->uid);								
						} else {
							
							$gotLon = $cachedLon;
							$gotLat = $cachedLat;
							$range = $cachedRange;
							
							$resultUseCache = true;
						}
					}
				} else {
					$gotLon = $appLbs->lon;
					$gotLat = $appLbs->lat;
					$range = 2*$appLbs->range;
					
					$appLbs->cacheLastResult($this->uid);
				}
			} else {
				$gotLon = $cached['lon'];
				$gotLat = $cached['lat'];
				$range = $cached['range'];
				
				$resultUseCache = true;
			}
			
			if (!$this->isValidLL($gotLon, $gotLat)) {
				if (!$appLbs->error && $this->isValidLL($appLbs->lon, $appLbs->lat)) {
					$gotLon = $appLbs->lon;
					$gotLat = $appLbs->lat;
					$range = 2*$appLbs->range;
					
					$appLbs->cacheLastResult($this->uid);
				} else {
					$gotLon = $config->location->default_lon;
					$gotLat = $config->location->default_lat;
					$range = 999999999;
				}
			}
		}
		
		$result = array(
			'lon' => $gotLon,
			'lat' => $gotLat,
			'range' => $range,
			'use_cache' => $resultUseCache
			);
			
		$rLbs = Better_Registry::get('lbs_last');
		$rLbs['use_cache'] = $resultUseCache;
		$rLbs['lon'] = $gotLon;
		$rLbs['lat'] = $gotLat;
		$rLbs['range'] = $range;
		Better_Registry::set('lbs_last', $rLbs);			
		
		Better_Log::getInstance()->logInfo('Params:['.serialize($params).'], Result:['.serialize($result).']', 'user_ll');
		
		Better_Log::getInstance()->putData(array(
			'll_params' => &$params,
			'll_result' => &$result
			), 'user_poi_trace');
		
		return $result;
	}
	
	/**
	 * 检测是否是个有效的经纬度数值
	 * 
	 * @return bool
	 */
	protected function isValidLL($lon, $lat)
	{
		return Better_LL::isValidLL($lon, $lat);
	}
}
