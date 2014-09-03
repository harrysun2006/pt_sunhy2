<?php

class Ajax_PoisController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init(true);	
	}	
	
	public function tipsAction()
	{
		$lon = (float)$this->getRequest()->getParam('lon');
		$lat = (float)$this->getRequest()->getParam('lat');
		
		$user = $this->uid ? $this->user : Better_User::getInstance();
		$return = Better_Poi_Tips::getRangedTips(array(
			'page' => $this->page,
			'count' => 30,
			'lon' => $lon,
			'lat' => $lat,
			'range' => 50000,
			));
		
		$this->output['rows'] = Better_Output::filterBlogs($return['rows']);
		$this->output['count'] = $return['count'];
		$this->output['rts'] = Better_Output::filterBlogs($return['rts']);
		$this->output['pages'] = Better_Functions::calPages($return['count']);
		$this->output['page'] = $this->page;		
		
		$this->output();
	}
	
	public function poisAction()
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

		$range = (int)$this->getRequest()->getParam('range', 50000);	
		$keyword = trim($this->getRequest()->getParam('keyword', ''));
		$count = (int)$this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$order = trim($this->getRequest()->getParam('order', ''));
		$withoutMine = (bool)$this->getRequest()->getParam('without_mine', 0);
		$wifiRange = (float)$this->getRequest()->getParam('wifi_range', 0);
		$withMajors = (bool)$this->getRequest()->getParam('with_major', 0);
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
			'uid' => $this->uid,
			'method' => $this->ft() ? 'fulltext' : 'mysql'
			))->search();

		$this->output = array_merge($this->output, $result);
		$this->output['pages'] = Better_Functions::calPages($result['total'], $count);
		$this->output['page'] = $this->page;		
		$this->output['rows'] = Better_Output::filterPois($this->output['rows'], true);

		if ($withMajors) {
			$tmp = Better_Poi_Major::majors(array(
				'lon' => $lon,
				'lat' => $lat,
				'range' => 50000,
				'page' => 1,
				'limit' => 35,
				));
			$this->output['majors'] = Better_Output::filterUsers($tmp['rows']);
		}
		
		$this->output();
	}
	
	public function majorsAction()
	{
		$lon = (float)$this->getRequest()->getParam('lon');
		$lat = (float)$this->getRequest()->getParam('lat');
		
		$tmp = Better_Poi_Major::majors(array(
			'lon' => $lon,
			'lat' => $lat,
			'range' => 50000,
			'page' => 1,
			'limit' => 180,
			));
		foreach ($tmp as $k=>$v) {
			$this->output[$k] = $v;
		}
		$this->output['rows'] = Better_Output::filterUsers($this->output['rows']);

		$this->output();
	}
	
	public function usersAction()
	{
		$lon = (float)$this->getRequest()->getParam('lon');
		$lat = (float)$this->getRequest()->getParam('lat');
				
		$results = Better_Search::factory(array(
			'what' => 'user',
			'lon' => $lon,
			'lat' => $lat,
			'range' => 50000,
			'page' => $this->page,
			'count' => 30,
			'order_key' => 'distance'
			))->fuckingSearch();		

		$this->output['rows'] = &Better_Output::filterUsers($results['rows']);
		$this->output['page'] = $this->page;
		$this->output['count'] = $results['count'];
		$this->output['pages'] = $results['pages'];

		$this->output();
	}
}