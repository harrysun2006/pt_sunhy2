<?php

/**
 * 市场部活动特定控制器
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_MarketController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
	}		
	
	public function indexAction()
	{
		
	}
	
	/**
	 * 大声展地图
	 * 
	 */
	public function loudsharemapAction()
	{
		$this->xmlRoot = 'statuses';
		$lon = (float)$this->getRequest()->getParam('lon', 0);
		$lat = (float)$this->getRequest()->getParam('lat', 0);
		$range = (int)$this->getRequest()->getParam('range', 1000);
		$count = (int)$this->getRequest()->getParam('count', 10);
		
		$userObj = Better_User::getInstance($this->uid);
		
		$results = $userObj->blog()->getAllBlogs(array(
			'page' => $this->page,
			'page_size' => $count,
			'lon' => $lon,
			'lat' => $lat,
			'range' => $range,
			'uids' => array(186145),
			'type' => array(
				'normal', 'checkin'
				),
			));

		foreach ($results['rows'] as $row) {
			$this->data[$this->xmlRoot][]['status'] = $this->api->getTranslator('market_loudshare')->translate(array(
				'data' => &$row,
				));
		}
		
		$this->output();
	}
	
	/**
	 * 大声展活动
	 * 
	 */
	public function loudshareAction()
	{
		$this->xmlRoot = 'loudshare';
		
		$now = time();
		$d = array(
			'kai' => array(
				'shouts' => array(),
				'checkins' => array(),
				'tips' => array(),
				'photos' => '',
				'attach' => array(),
				'total_checkins' => 0,
				),
			'cms' => array(
				'daily_artists' => array(),
				'badges' => array(),
				),
			'date' => date('Y-m-d H:i:s',$now+3600*8),
			);

		$bjOvertime = $this->config->poi->getitlouder->bj->overtime;
		if ($bjOvertime>$now) {
			$poiId = $this->config->poi->getitlouder->bj->id;
		} else {
			$shOvertime = $this->config->poi->getitlouder->sh->overtime;
			$poiId = $this->config->poi->getitlouder->sh->id;
		}
		
		$poiId || $poiId = 122660;
		
		$poi = Better_Poi_Info::getInstance($poiId);
		$poiInfo = $poi->getBasic();
		$user = Better_User::getInstance();
		
		//	吼吼
		$rows = Better_Market_Getitlouder::getShouts();
		foreach ($rows as $row) {
			$d['kai']['shouts'][]['shout'] = array(
				'username' => $row['username'],
				'nickname' => $row['nickname'],
				'message' => $row['message'],
				'avatar' => $row['avatar_url'],
				'photo' => $row['attach_thumb'],
				);
		}
		
		//	签到
		$rows = Better_Market_Getitlouder::getCheckins();
		foreach ($rows as $row) {
			$d['kai']['checkins'][]['checkin'] = array(
				'username' => $row['username'],
				'nickname' => $row['nickname'],
				'message' => $row['message'] ? $row['message'] : '我在大声展!',
				'avatar' => $row['avatar_url'],
				'photo' => $row['attach_thumb'],
				);
		}					
		
		//	贴士
		$rows = Better_Market_Getitlouder::getTips();
		foreach ($rows as $row) {
			$d['kai']['tips'][]['tip'] = array(
				'username' => $row['username'],
				'nickname' => $row['nickname'],
				'message' => $row['message'],
				'avatar' => $row['avatar_url'],
				'photo' => $row['attach_thumb'],
				);
		}
		
		//	用户
		$rows = Better_Market_Getitlouder::getUsers();
		foreach ($rows as $row) {
			$d['kai']['photos'][]['photo'] = array(
				'nickname' => $row['nickname'],
				'username' => $row['username'],
				'avatar' => $row['avatar_url']
				);
		}
		
		//	单个附件
		$rows = Better_Market_Getitlouder::getAttach();
		if (is_array($rows) && count($rows)>0) {
			$row = array_pop($rows);
			$d['kai']['attach'] = array(
				'username' => $row['username'], 
				'nickname' => $row['nickname'],
				'url' => $row['attach_thumb'],
				);
		}
		
		//	签到数
		$d['kai']['total_checkins'] = $poiInfo['checkins'];
		
		//	艺术家
		$d['cms']['daily_artists'][]['photo'] = 'http://k.ai/images/badges/big/69.png';
		
		//	勋章
		$md = date('md', time()+3600*8);
		$row = Better_DAO_Badge::getInstance()->get(array(
			'class_name' => 'Getitlouder'.$md
			));

		if ($row['id']) {
			$dbid = $row['id'];
		} else {
			$dbid = 94;
		}
		$dailyBadge = BETTER_BASE_URL.'/images/badges/big/'.$dbid.'.png';
		$d['cms']['badges'][]['badge'] = $dailyBadge;
		
		$this->data[$this->xmlRoot] = &$d;
		
		$this->output();
	}
	
	/**
	 * 中秋节活动
	 * 
	 */
	public function midautumnAction()
	{
		$this->xmlRoot = 'photos';
		
		$cacher = Better_Cache::remote();
		$result = $cacher->get('market_midautumn');
		
		if (!$result) {
			$result = Better_Market_Midautumn::recUsers(array(
				'page' => $this->page,
				'count' => $this->count,
				'public' => true,
				'uid' => BETTER_SYS_UID,
				'has_avatar' => true,
				));		
			if (count($results['rows'])<$this->count) {
				$cacher->set('market_midautumn', null);
			} else {
				$cacher->set('market_midautumn', $result, 300);
			}
		}
			
		foreach ($result['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'photo' => $this->api->getTranslator('market_midautumn')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);
		}
		
		$this->output();
	}
}