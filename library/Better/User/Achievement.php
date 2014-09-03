<?php

/**
 * 用户成就
 * 用来解析一个页面中用户可能获得Karma、勋章、掌门
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Achievement extends Better_User_Base
{
	protected static $instance = array();
	protected $user = null;

	public static function getInstance($uid)
	{

		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}

	protected function __construct($uid)
	{
		$this->uid = $uid;
		$this->user = Better_User::getInstance($uid);
	}
	
	/**
	 * 
	 * 给客户端用的成就解析
	 */
	public function apiParse()
	{
		$karma = $this->user->karma()->getDelta();
		$badges = $this->user->badge()->getDelta();
		$rp = $this->user->rp()->getDelta();
		$lang = Better_Language::loadIt(Better_Registry::get('language'));
		$checkinMsg = Better_Registry::get('checkin_msg');

		$result = array(
			'badges' => $badges,
			'karma' => $rp
			);
			
		return $result;
	}
	
	/**
	 * 分析用户成就信息
	 * 一般在页面主要逻辑处理完毕，即将向浏览器输出内容前调用
	 * 
	 * @return array
	 */
	public function parse($prefix='')
	{
		$karma = $this->user->karma()->getDelta();
		$badges = $this->user->badge()->getDelta();
		$major = $this->user->major()->getDelta();
		$rp = $this->user->rp()->getDelta();
		$lang = Better_Language::loadIt(Better_Registry::get('language'));
		$checkinMsg = Better_Registry::get('checkin_msg');

		$result = array();
		
		if (trim($checkinMsg)!='') {
			$result[] = $checkinMsg;
		} else {
			//现在系统中karma为我们说的RP值，系统中的RP为我们前台所看到的KARMA
			/* 
			if ($karma>0) {
				$result[] = str_replace('{KARMA}', Better_Karma::format($karma), $lang->global->sketch->delta->karma); 
			} else if ($karma<0) {		
				$tempkarma = abs($karma);
				$result[] = str_replace('{KARMA}', Better_Karma::format($tempkarma), $lang->global->sketch->delta->reducekarma);
			}
			*/
			if ($rp>0) {
				$result[] = str_replace('{RP}', Better_Rp::format($rp), $lang->global->sketch->delta->rp); 
			} else if ($rp<0) {		
				$temprp = abs($rp);
				$result[] = str_replace('{RP}', Better_Rp::format($temprp), $lang->global->sketch->delta->reducerp);
			}
			if (count($badges)>0) {
				foreach ($badges as $badgeId) {
					$badge = Better_Badge::getBadge($badgeId);
					$result[] = str_replace('{BADGE}', ' "'.$badge->badge_name.'"', $lang->global->sketch->delta->badge);
				}
			}
			
			if ($major>0) {
				$poiInfo = Better_Poi_Info::getInstance($major)->getBasic();
				
				$result[] = str_replace('{POI}', '"'.$poiInfo['name'].'"', $lang->global->sketch->delta->major);
			}
		}
		
		if (Better_Registry::get('no_avatar')) $result[] = Better_Registry::get('no_avatar');
		return $result;
	}
	
	public function apiParseNew($prefix='')
	{
		$karma = $this->user->karma()->getDelta();
		$badges = $this->user->badge()->getDelta();
		$major = $this->user->major()->getDelta();
		$rp = $this->user->rp()->getDelta();
		$lang = Better_Language::loadIt(Better_Registry::get('language'));
		$checkinMsg = Better_Registry::get('checkin_msg');

		$result = array();
		
		if (trim($checkinMsg)!='') {
			$result[] = $checkinMsg;
		} else {
			if ($rp>0) {
				$result[] = $prefix.str_replace('{RP}', intval($rp), $lang->global->sketch->delta->rp); 
			} else if ($rp<0) {		
				$temprp = abs($rp);
				$result[] = $prefix.str_replace('{RP}', intval($temprp), $lang->global->sketch->delta->reducerp);
			}
			
			if (count($badges)>0) {
				$bs = array();
				foreach ($badges as $badgeId) {
					$badge = Better_Badge::getBadge($badgeId);
					$bs[] = $badge->badge_name;
				}
				$result[] = $lang->global->got_badge.' '. '"'.implode('","', $bs).'"';
			}
			
			if ($major>0) {
				$poiInfo = Better_Poi_Info::getInstance($major)->getBasic();
				
				$result[] = str_replace('{POI}', '"'.$poiInfo['name'].'"', $lang->global->sketch->delta->major);
			}
		}

		return $result;		
	}
}