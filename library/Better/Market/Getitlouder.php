<?php

/**
 * 大声展活动
 * 
 * @package Better.Market
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Market_Getitlouder extends Better_Market_Base
{
	protected static $inited = false;
	protected static $poiId = 122660;
	protected static $data = array();
	protected static $users = array();
	protected static $config = null;
	protected static $lastChecked = array();
	protected static $lastUidChecked = array();
	
	protected static function init()
	{
		if (!self::$inited) {
			self::$config = Better_Config::getAppConfig();
			$bjOvertime = self::$config->poi->getitlouder->bj->overtime;

			if ($bjOvertime>time()) {
				self::$poiId = self::$config->poi->getitlouder->bj->id;
			} else {
				self::$poiId = self::$config->poi->getitlouder->sh->id;
			}

			self::$data = Better_DAO_Market_Blog::getAllData(self::$poiId);
			self::$users = Better_DAO_Market_Blog::getAllUser(self::$poiId);
			self::$lastChecked = Better_DAO_Market_Blog::getAllChecked(self::$poiId);
			self::$lastUidChecked = Better_DAO_Market_Blog::getAllUidChecked(self::$poiId);

			self::$inited = true;
		}
	}
	
	public static function getShouts()
	{
		return self::getCommon('normal');
	}
	
	public static function getCheckins()
	{
		return self::getCommon('checkin');	
	}
	
	public static function getTips()
	{
		return self::getCommon('tips');
	}
	
	protected function getCommon($type)
	{
		self::init();
		
		$poi = Better_Poi_Info::getInstance(self::$poiId);
		$poiInfo = $poi->getBasic();
		$user = Better_User::getInstance();
		
		$rows = array();
		$page = 1;

		if (count(self::$data)>0) {
			while(count($rows)<5 && $page<20) {
				$tmp = $user->blog()->getAllBlogs(array(
					'page' => $page,
					'page_size' => 5,
					'type' => $type,
					'poi' => self::$poiId,
					'only_avatar' => true
					));

				foreach ($tmp['rows'] as $row) {
					if ($row['avatar']) {
						$avatarUrl = $row['avatar_url'];
						$avatarSuffix = str_replace(BETTER_BASE_URL.'/files/', '', $avatarUrl);
						$avatarPath = self::$config->attachment->attach_server_1->save_path.'/'.$avatarSuffix;
						$avatarTime = file_exists($avatarPath) ? filectime($avatarPath) : time();
						
						if (in_array($row['bid'], self::$data) && !isset($rows[$row['bid']])) {
							if ($row['dateline']>$avatarTime || self::$lastChecked[$row['bid']]>$avatarTime) {
								$rows[$row['bid']] = $row;
							} else {
								Better_DAO_Market_Blog::getInstance($row['uid'])->updateByCond(array(
									'kai_checked' => 0,
									'partner_checked' => 0
									), array(
									'bid' => $row['bid'],
									'uid' => $row['uid'],
									));
							}
						}
					}
				}
					
				$page++;
			}
		}
		
		if (count($rows)>5) {
			$tmp = array_chunk($rows, 5);
			$rows = $tmp[0];
		}

		return $rows;		
	}
	
	public static function getUsers()
	{
		self::init();
		
		$poi = Better_Poi_Info::getInstance(self::$poiId);
		$poiInfo = $poi->getBasic();
		$user = Better_User::getInstance();
		
		$rows = array();
		$page = 1;

		if (count(self::$users)>0) {
			while(count($rows)<5 && $page<10) {
				$tmp = Better_Poi_Checkin::getInstance(self::$poiId)->users($page, 10, true);

				foreach ($tmp['rows'] as $row) {
					if ($row['avatar']) {
						$avatarUrl = $row['avatar_url'];
						$avatarSuffix = str_replace(BETTER_BASE_URL.'/files/', '', $avatarUrl);
						$avatarPath = self::$config->attachment->attach_server_1->save_path.'/'.$avatarSuffix;
						$avatarTime = file_exists($avatarPath) ? filectime($avatarPath) : time();
						
						if (in_array($row['uid'], self::$users) && !isset($rows[$row['uid']])) {
							if ($row['status']['dateline']>$avatarTime || self::$lastUidChecked[$row['uid']]>$avatarTime) {
								$rows[$row['uid']] = $row;
							} else {
								Better_DAO_Market_Blog::getInstance($row['uid'])->updateByCond(array(
									'kai_checked' => 0,
									'partner_checked' => 0
									), array(
									'uid' => $row['uid'],
									));
							}
						}
					}
				}
					
				$page++;
			}
		}

		if (count($rows)>5) {
			$tmp = array_chunk($rows, 5);
			$rows = $tmp[0];
		}
				
		return $rows;				
	}
	
	public static function getAttach()
	{
		self::init();
		
		$poi = Better_Poi_Info::getInstance(self::$poiId);
		$poiInfo = $poi->getBasic();
		$user = Better_User::getInstance();
		
		$rows = array();
		$page = 1;

		if (count(self::$data)>0) {
			while(count($rows)<1 && $page<20) {
				$tmp = $user->blog()->getAllBlogs(array(
					'page' => $page,
					'page_size' => 1,
					'type' => array('normal', 'checkin', 'tips'),
					'poi' => self::$poiId,
					'has_attach' => true
					));

				foreach ($tmp['rows'] as $row) {
					if ($row['avatar'] && $row['avatar_thumb']) {
						$avatarUrl = $row['avatar_url'];
						$avatarSuffix = str_replace(BETTER_BASE_URL.'/files/', '', $avatarUrl);
						$avatarPath = self::$config->attachment->attach_server_1->save_path.'/'.$avatarSuffix;
						$avatarTime = file_exists($avatarPath) ? filectime($avatarPath) : time();
						
						if (in_array($row['bid'], self::$data) && !isset($rows[$row['bid']])) {
							if ($row['dateline']>$avatarTime || self::$lastChecked[$row['bid']]>$avatarTime) {
								$rows[$row['bid']] = $row;
							} else {
								Better_DAO_Market_Blog::getInstance($row['uid'])->updateByCond(array(
									'kai_checked' => 0,
									'partner_checked' => 0
									), array(
									'bid' => $row['bid'],
									'uid' => $row['uid'],
									));
							}
						}
					}
				}
					
				$page++;
			}
		}

		return $rows;				
	}
}