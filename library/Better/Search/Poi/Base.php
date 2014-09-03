<?php

/**
 * POI搜索
 * 
 * @package Better.Search.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Search_Poi_Base extends Better_Search_Base
{

	protected function __construct(array $params)
	{
		parent::__construct($params);
	}	

	protected function parseResults()
	{
		
	}

	/**
	* 搜索foursquare poi数据
	*
	* @return array
	*/
	protected function _search4sq()
	{
	  $result = array(
	    'total' => 0,
	    'rows' => array(),
	    'count' => 0,
	    'pages' => 0,
	    'emails' => array(),
	  );
	  try {
	    // 是否开启fsq服务?
	    $enabled = Better_Config::getAppConfig()->service->fsq->enabled;
	    if (!$enabled) return $result;
	    // 是否仅在国外开启fsq?
	    $outchina = Better_Config::getAppConfig()->service->fsq->outchina;
	    $p = array($this->params['lon'], $this->params['lat']);
	    if ($outchina && Better_Geo::isInChina($p)) return $result;

  	  /**
  	   * foursquare的限制为每用户每小时(moving window)不超过500次请求. 
  	   * 考虑到可能会有同一IP请求次数限制, 此处使用cache进行简单的保护.
  	   */
  	  $hour_limit = Better_Config::getAppConfig()->service->fsq->hour_limit;
  	  $key = 'fsq_search';
  	  if (!$hour_limit) $hour_limit = 6000;
  	  $cache = Better_Cache::remote();
      $fsq_search = $cache->get($key); // array('start' => time(), 'count' => ...)
      $now = time();
      if (!$fsq_search || $now - $fsq_search['start'] > 3600) {
        $fsq_search = array(
          'start' => $now,
          'count' => 0,
        );
      } elseif ($fsq_search['count'] >= $hour_limit) {
        return $result;
      }
      $fsq_search['count']++;
      $cache->set($key, $fsq_search);
  
      // 查询foursquare.com API
      $params = array_merge($this->params, array());
      $pois = Better_Service_4sq_Poi::search($params);
      foreach ($pois['rows'] as &$row) {
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
      }
  	  $result = array_merge($result, $pois);
	  } catch (Exception $e) {
	    Better_Log::getInstance()->logAlert('4sq_poi_search_exception:' . $e->getMessage(), '4sq_poi_error');
	  }
    return $result;
	}

}