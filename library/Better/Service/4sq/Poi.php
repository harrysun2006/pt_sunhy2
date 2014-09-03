<?php
/**
 * 4sq POI 搜索接口
 * 
 * @package Better_Service_4sq
 * @author  yangl
 */

class Better_Service_4sq_Poi
{

	/**
	 * 搜索4sq poi, 如果4sq返回的['meta']['errorType']=='rate_limit_exceeded', 则判为超限
	 */
	public static function search(array $params)
	{
	  $return = array(
  	  'total' => 0,
  	  'rows' => array(),
  	  'count' => 0,
  	  'errorCode' => '',
	  );
	  $fsq = Better_Config::getAppConfig()->service->fsq;
		$max_try = $fsq->max_try;
		!$max_try && $max_try = 3;
		$try = 0;
		while ($try < $max_try) {
		  $token = Better_Service_4sq_Token::get();
		  if (!$token) return $return;
		  $return = self::_search($params, $token);
		  if ($return['errorCode'] == 'rate_limit_exceeded') {
		    Better_Service_4sq_Token::evict($token);
		  }
		  if (!$return['errorCode']) break;
		  $try++;
		} 
    return $return;
	}

	private static function _search(array $params, $token)
	{
	  $lon = $params['lon'];
	  $lat = $params['lat'];
	  $ll = $lat.','.$lon;
	  $query = $params['query'] ? $params['query'] : '';
	  // 使用https协议可以不使用代理访问4sq
	  /*
	   $hosts = explode('|', Better_Config::getAppConfig()->ssh->proxy->hosts);
	  $host = $hosts[0];
	  list($ip, $port) = explode(':', $host);
	  $config = array(
	  'adapter'    => 'Zend_Http_Client_Adapter_Proxy',
	  'proxy_host' => $ip,
	  'proxy_port' => $port
	  );
	  */
	  $config = array(
	  	'timeout' => 10,
	  );
	  $fsq = Better_Config::getAppConfig()->service->fsq;
	  $api_url = $fsq->search_api;
	  !$api_url && $api_url = 'https://api.foursquare.com/v2/venues/search';
	  $radius = $params['range'];
	  $version = $fsq->version;
	  !$version && $version = '20110912';
	  // 根据官方API说明llAcc默认=10000, 目前此参数不影响结果, 因而未加入
	  $url = $api_url . '?ll=' . $ll . '&query=' . urlencode($query)
	    . '&oauth_token=' . $token['oauth_token'] . '&limit=50' 
	    . '&radius=' . $radius . '&v=' . $version;
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	  curl_setopt($ch, CURLOPT_HEADER, 0);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Connection: Keep-Alive',
			'Cache-Control: no-cache',
			'Accept: */*',
			'User-Agent: Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.215 Safari/535.1',
	  ));
	  $res = curl_exec($ch);
	  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	  $errorCode = '';
	  $pois = array();
	  $rows = array();
	  $stat = array('found' => 0, 'saved' => 0, 'old' => 0, 'new' => 0);
	  if ($httpCode == 200) {
	    $result = json_decode($res, true);
	    $metaCode = $result['meta']['code'];
	    if($metaCode == 200) {
	      if ($result['response']['venues']) {
	        // https://api.foursquare.com/v2/venues/search
	        $pois = $result['response']['venues'];
	      } elseif ($result['response']['groups'][0]['items']) {
	        // https://api.foursquare.com/v2/venues/explore
	        foreach ($result['response']['groups'][0]['items'] as $item) {
	          if ($item['venue'])
	            $pois[] = $item['venue'];
	        }
	      }
	      if ($pois && count($pois) > 0) {
	        $rows = self::_save($pois, $stat);
	      }
	    } else {
	      $errorType = $result['meta']['errorType'];
	      $errorDetail = $result['meta']['errorDetail'];
	      $errorCode = $errorType ? $errorType : ($metaCode ? $metaCode : 'APIX');
	      Better_Log::getInstance()->logAlert('api call failed:['
	      . 'url:' . $url
	      . ', res:' . $res . ']', '4sq_poi_error');
	    }
	  } else {
	    $errorCode = 'GETX.' . $httpCode;
	    Better_Log::getInstance()->logAlert('connect failed:['
	      . 'url:' . $url
	      . ', code:' . $errorCode . ']', '4sq_poi_error');
	  }
		$return = array(
		  'total' => count($rows),
		  'rows' => $rows,
		  'count' => count($rows),
		  'errorCode' => $errorCode,
		);
		if (!$errorCode) {
  		Better_Log::getInstance()->logInfo('got fsq pois: ['
  		  . 'url:' . $url
  		  . ', found:' . $stat['found']
  		  . ', saved:' . $stat['saved']
  		  . ', old:' . $stat['old']
  		  . ', new:' . $stat['new']
  		  . ']', '4sq_poi_info');
		}
		return $return;
	}

	// 保存到临时表
	private static function _save($pois, &$stat)
	{
	  $rows = array();
	  $stat['found'] = count($pois);
		foreach ($pois as $poi) {
		  $vrow = Better_DAO_Poi_Foursquare::getInstance()->get4sqPoi($poi['id']);
		  if (!$vrow || !$vrow['id']) {
  			$params = array();
  			$params['id'] = $poi['id'];
  			$params['name'] = $poi['name'];	
  			$params['phone'] = $poi['contact']['phone'] ? $poi['contact']['phone'] : '';
  			$params['address'] = $poi['location']['address'] ? $poi['location']['address'] : '';
  			$params['city'] = $poi['location']['city'] ? $poi['location']['city'] : '';
  			$params['state'] = $poi['location']['state'] ? $poi['location']['state'] : '';
  			$params['country'] = $poi['location']['country'] ? $poi['location']['country'] : '';
  			$params['lon'] = $poi['location']['lng']!=='' ? $poi['location']['lng'] : 0;
  			$params['lat'] = $poi['location']['lat']!=='' ? $poi['location']['lat'] : 0;
  			list($x, $y) = Better_Functions::LL2XY($params['lon'], $params['lat']);
  			$params['x'] = $x;
  			$params['y'] = $y;
  			$cat = null;
  			foreach ($poi['categories'] as $_cat) {
  			  if (is_null($cat)) {
  			    $cat = $_cat;
  			  }
  			  if ($_cat['primary']) {
  			    $cat = $_cat;
  			    break;
  			  }
  			}
  			$params['category_id'] = $cat ? $cat['id'] : '';
  			$params['category_name'] = $cat['parents'][0] ? $cat['parents'][0] : '';
  			$params['category_icon'] = $cat ? $cat['icon'] : '';
  			$params['verified'] = $poi['verified'];
  			$params['cnt_checkins'] = $poi['stats']['checkinsCount'] ? (int) $poi['stats']['checkinsCount'] : 0;
  			$params['cnt_users'] = $poi['stats']['usersCount'] ? (int) $poi['stats']['usersCount'] : 0;
  			$params['cnt_tips'] = $poi['stats']['tipCount'] ? (int) $poi['stats']['tipCount'] : 0;
  			// $params['poi_id'] = 0; // 和kai的poi关联
  			// $params['info'] = serialize($poi); // 4sq poi的序列化
  			$params['create_time'] = time();
  			Better_DAO_Poi_Foursquare::getInstance()->save($params);
			  $stat['saved']++;
		  }
			$krow = Better_Service_4sq_Pool::fsq2our($poi['id']);
			$krow['#new'] ? $stat['new']++ : $stat['old']++; 
			$rows[] = $krow;
		}
		return $rows;
	}

	/**
	 * 4sq poi与kaikai poi分类建立对应关系
	 */
	public static function fsqcat2kai($fsqcat)
	{
		$map = array(
			'Arts & Entertainment' => 3,  //娱乐
			'College & University' => 6,  //文化
			'Food'                 => 1,  //食宿,
			'Great Outdoors'       => 9,  //户外,
			'Home, Work, Other'    => 7,  //服务
			'Nightlife Spot'       => 8,  //办公
			'Shop'                 => 2,  //购物
			'Travel Spot'          => 10, //出行
		);
		return ($fsqcat && isset($map[$fsqcat]))? $map[$fsqcat] : 11; //其他
	}

}