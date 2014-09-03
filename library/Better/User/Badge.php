<?php

/**
 * 用户勋章
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Badge extends Better_User_Base
{
	protected static $instance = array();
	
	public $badges = array();
	public $syncbadges = array();
	protected $delta = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
    public function getDelta()
    {
    	return $this->delta;
    }	
    
    /**
     * 获取我的某个勋章详情
     * 
     * @return array
     */
    public function getBadge($id)
    {
    	$this->getUserInfo();
    	
    	$tmp = Better_DAO_User_Badge::getInstance($this->uid)->get(array(
    		'uid' => $this->uid,
    		'bid' => $id
    		));
    	if ($tmp['bid']) {
	    	$badges = Better_Badge::getAllBadges();
	    	$badge = $badges[$tmp['bid']]->getParams();
	    	$poi = Better_Poi_Info::getInstance($tmp['poi_id']);
	    	
	    	$badge['get_time'] = $tmp['get_time'];
	    	$badge['poi'] = $poi->getBasic();
	    	$badge['exchanged'] = Better_DAO_Badge_Exchange_Log::getInstance($this->uid)->exchanged($this->uid, $tmp['bid']);
    	} else {
	    	$badges = Better_Badge::getAllBadges();
	    	$badge = $badges[$id]->getParams();
	    	
	    	$badge['poi_name'] = '';
	    	$badge['poi_id'] = '';
	    	$badge['get_time'] = '';    		
	    	$badge['exchanged'] = false;
    	}
    	
    	return $badge;
    }
	
	public function getAlbum($preview, $previewCount=0)
	{
		$album = array();
		$badges = $this->getMyBadges();
		foreach (Better_Badge::$families as $key=>$value) {
			$album[$key]['totoal'] = 0;
			$album[$key]['unlocked'] = 0;
		}
		
		foreach ($badges as $badge) {
			if ($preview && (count($album[$badge['family']]['badges']) < $previewCount)) {
				$album[$badge['family']]['badges'][] = $badge;
			}
			$album[$badge['family']]['unlocked']++;
		}
		return $album;
	}
	
	/**
	 * 获取某个分类的勋章
	 */
	public function &getByFamily($family)
	{
		$allBadges = $this->getMyBadges();

		$result = array();
		if ($family == 'all') {
			$result = $allBadges;
		} else {
			foreach ($allBadges as $badge) {
				if ($badge['family'] == $family) {
					$result[] = $badge;
				}
			}
		}
		return $result;
	}
	
	/**
	 * 获取当前用户所有的勋章
	 * 
	 * @return array
	 */
	public function &getMyBadges()
	{
		$sessUid = Better_Registry::get('sess')->get('uid');
		$result = array();
		$userInfo = $this->getUserInfo();
		
		//if ($sessUid==$this->uid || ($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($userInfo['priv']=='protected' && $this->user->isFriend($sessUid))))) {
			$badges = Better_Badge::getAllBadges();
			
			if (count($this->badges)==0) {
				$cacheKey = 'user_badges_'.$this->uid;
				$cacher = Better_Cache::remote();
				$this->badges = $cacher->get($cacheKey);
				
				if (!$this->badges) {
					$this->badges = array();
					$tmp = Better_DAO_User_Badge::getInstance($this->uid)->getUserBadges();
					
					$tmp2 = array();
					foreach ($tmp as $row) {
						$tmp2[$row['get_time'].'.'.($row['bid']+1000)] = $row;
					}
					//ksort($tmp2, SORT_NUMERIC);
	
					$poiIds = array();
					foreach ($tmp2 as $row) {
						
						if (isset($badges[$row['bid']]) && $badges[$row['bid']]) {
							$badge = $badges[$row['bid']]->getParams();
							$poiIds[$row['bid']] = $row['poi_id'];
							
							$badge['poi_id'] = $row['poi_id'];
							$badge['get_time'] = $row['get_time'];
							$badge['exchanged'] = $row['exchanged'] ? true : false;
							$badge['poi_name'] = '';
							$badge['poi'] = array();
							
							$this->badges[$row['bid']] = $badge;
						} else {
							Better_Log::getInstance()->logAlert('Badge_Not_Exists:['.$row['bid'], 'badge_error');
						}
					}
					
					if (count($poiIds)>0) {
						$tmp = Better_DAO_Poi_Search::getInstance()->search(array(
							'poi_id' => $poiIds
							));
						$pois = array();
						foreach ($tmp['rows'] as $row) {
							Better_Poi_Info::createInstance($row);
						}
						
						foreach ($this->badges as $bid=>$v) {
							if ($bid) {
								$poiInfo = Better_Poi_Info::getInstance($v['poi_id'])->getBasic();
								$v['poi_name'] = $poiInfo['name'];
								$v['poi'] = $poiInfo;
								$this->badges[$bid] = $v;
							}
						}
					}
					
					$cacher->set($cacheKey, $this->badges);
				} else {
					foreach ($this->badges as $bid=>$badge) {
						$params = $badges[$bid]->getParams();
						$badge['remain'] = $params['remain'];
						$this->badges[$bid] = $badge;
					}
				}
			}

			$result = &$this->badges;
		//}

		return (array)$result;
	}
	
	/**
	 * 获得某个勋章
	 * 
	 * @param $badgeId
	 * @return bool
	 */
	public function got($badgeId, $poiId=0)
	{
		$result = false;
		$this->getMyBadges();
		
		if (!array_key_exists($badgeId, $this->badges)) {
			$this->delta[] = $badgeId;
			
			$this->getUserInfo();
			$poiId = (int)$poiId;
			
			$tmp = Better_DAO_User_Badge::getInstance($this->uid)->get(array(
				'uid' => $this->uid,
				'bid' => $badgeId
				));
			
			if (!$tmp['uid']) {
				$flag = Better_DAO_User_Badge::getInstance($this->uid)->insert(array(
					'uid' => $this->uid,
					'bid' => $badgeId,
					'get_time' => time(),
					'poi_id' => $poiId ? $poiId : ''
					));	
				if ($flag) {
					$badgeCnt = Better_DAO_User_Badge::getInstance($this->uid)->getBadgeCntByUid($this->uid);
					$userInfo = $this->user->getUserInfo();
					$this->user->updateUser(array(
						'badges' => $badgeCnt,
						));
					$this->badges[$badgeId] = Better_Badge::getBadge($badgeId)->getParams();
					$this->syncbadges[$badgeId] = Better_Badge::getBadge($badgeId)->getParams();//同步的勋章

					//更新勋章的得到时间
					Better_Badge::logBadge($badgeId);
					
					$cacher = Better_Cache::remote();
					$cacheKey = 'user_badges_'.$this->uid;
					$cacher->set($cacheKey, null);
					
					//if ($userInfo['sync_badge']) {
						/*Better_Hook::factory(array(
							'Syncsites'
							))->invoke('GetBadge', array(
								'uid' => $this->uid,
								'badge_id' => $badgeId,
								'poi_id' => $poiId
							));*/
					//}
					
					//获得勋章自动发一条吼吼
					$this->user->blog()->add(array(
						'message' => '获得 '.$this->badges[$badgeId]['badge_name'].' 勋章',
						'priv' => 'public',
						'upbid' => 0,
						'poi_id' => $poiId ? $poiId : '',
						'need_sync' => 0,
						'passby_spam' => 1,
						'nokarma' => 1,
						'passby_filter' => 1,
						'source'=>'',
						'badge_id'=> $badgeId
					));
					if(Better_Config::getAppConfig()->market->wlan->switch){
						$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();									
						if($badgeId>=301 && $badgeId<=310){										
							$badgeInfo = Better_Badge::getBadge($badgeId)->getParams();				
							if ($badgeInfo['badge_name']) {
								$langKey = Better_Registry::get('language');
								if (preg_match('/en/i', $langKey)) {
									$syncTips = $badgeInfo['en_sync_tips'];
								} else {
									$syncTips = $badgeInfo['sync_tips'];
								}					
								if (!$syncTips) {
									$msg = Better_Registry::get('lang')->global->sync_got_badge;
									$msg = str_replace('{BADGE}', $badgeInfo['badge_name'], $msg);
									//$msg .= " ".Better_User::getInstance($uid)->getUserLang()->global->badge_sync_suffix;
								} else {
									$msg = $syncTips;
								}					
							}							
							list($lon,$lat) = Better_Functions::XY2LL($poiInfo['x'], $poiInfo['y']);			
							$attach = "http://k.ai/images/badges/big/".$badgeId.".png";
							$wlandate = array(
								'nickname' =>$userInfo['nickname'],
								'img' =>$userInfo['avatar_url'],
								'content' =>$msg,
								'photo' =>$attach,
								'lon' =>$lon,
								'lat' =>$lat,
								'posttm' =>time(),
								'checktm' =>time(),
								'synctm' =>0,					
								'status' =>1
							);				
							Better_DAO_Wlanblog::getInstance()->insert($wlandate);	
						}
					}
				}
				
				$result = true;
			}
		}
		
		return $result;
		
	}
	
	/**
	 * 兑换某勋章
	 * 
	 * @param unknown_type $bid
	 * @return array
	 */
	public function exchange($bid, $sCode)
	{
		$codes = array(
			'SUCCESS' => 1,
			'FAILED' => 0,
			'EXPIRED' => -1,
			'NO_REMAINS_LEFT' => -2,
			'EXCHANGED' => -3,
			'NOT_HAVE' => -4,
			'CODE_WRONG' => -5
			);
		$code = $codes['FAILED'];
		
		if (Better_DAO_Badge_Exchange_Log::getInstance($this->uid)->exchanged($this->uid, $bid)) {
			$code = $codes['EXCHANGED'];
		} else {
			$this->getMyBadges();
			
			if (!array_key_exists($bid, $this->badges)) {
				$code = $codes['NOT_HAVE'];
			} else {
				$badge = $this->badges[$bid];
				$row = Better_DAO_Badge_Exchange::getInstance()->get($bid);
				$badge['remain'] = $row['remain'];
				$badge['expire_at'] = $row['expire_at'];
				$badge['code'] = $row['code'];
				
				if ($badge['remain']<=0) {
					$code = $codes['NO_REMAINS_LEFT'];
				} else if (time()>$badge['expire_at']) {
					$code = $codes['EXPIRED'];
				} else if ($sCode!=$badge['code']) {
					$code = $codes['CODE_WRONG'];
				} else {
					Better_DAO_Badge_Exchange_Log::getInstance($this->uid)->insert(array(
						'uid' => $this->uid,
						'badge_id' => $bid,
						'dateline' => time()
						));
					Better_DAO_Badge_Exchange::getInstance()->update(array(
						'remain' => $badge['remain']-1
					), array(
						'badge_id' => $bid
						));
						
					//	重设勋章缓存
					Better_Cache::remote()->set('kai_badges', null);
						
					$code = $codes['SUCCESS'];
				}
			}
			
		}
		
		return array(
			'code' => $code,
			'codes' => &$codes
			);
	}
	
	/**
	 * 获取我的勋章总数
	 * 
	 * @return integer
	 */
	public function getBadgesnum()
	{	
		
		$tmp = Better_DAO_User_Badge::getInstance($this->uid)->getAll(array(
					'uid' => $this->uid
					));		
		
		$retrun = count($tmp);
		return $retrun;
	}
	
	
	/**
	 * 析构 时勋章同步
	 */
	public function __destruct(){
		$badges = $this->syncbadges;
		if($badges && is_array($badges)){
			$badgeIds =  array_keys($badges);
			if(count($badges)==1){
				$badgeId = $badgeIds[0];
				Better_Hook::factory(array(
						'Syncsites'
					))->invoke('GetBadge', array(	
							'uid' => $this->uid,
							'badge_id' => $badgeId,
					));
			}else if(count($badges)>1){
				$badgeId = 0;
				$_badges = array('partner'=> array(), 'event'=>array(), 'memorial'=>array(), 'normal'=>array());
				foreach($badges as $bid=>$badge){
					if($badge['family']=='partner'){
						$_badges['partner'][] = $bid;
					}else if($badge['family']=='event'){
						$_badges['event'][] = $bid;
					}else  if($badge['family']=='memorial')	{
						$_badges['memorial'][] = $bid;
					}else if($badge['family']=='explore'){
						$_badges['explore'][] = $bid;
					}else if($badge['family']=='normal'){
						$_badges['normal'][] = $bid;
					}
				}
				
				foreach($_badges as $k=>$row){
					if($row){
						$badgeId = $row[0];
						break;
					}
				}
				
				Better_Hook::factory(array(
						'Syncsites'
					))->invoke('GetBadge', array(	
							'uid' => $this->uid,
							'badge_id' => $badgeId,
							'badges'=> $badges
					));
			}
		}
	}
}