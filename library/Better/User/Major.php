<?php

/**
 * 
 * 用户皇帝/皇冠
 * 
 * @package Better.User
 * @author leip
 *
 */
class Better_User_Major extends Better_User_Base
{
	private static $instance = array();
	protected $majors = array();
	protected $delta = 0;

    public static function getInstance($uid=0)
    {
    	$uid=='' && $uid=0;
        if (!array_key_exists($uid, self::$instance)) {
            self::$instance[$uid] = new self($uid);
        }
        return self::$instance[$uid];
    }	
    
    public function getDelta()
    {
    	return $this->delta;
    }
    
    /**
     * 
     * 网页右边栏特别处理
     */
    public function &rightbar()
    {
   		$this->getUserInfo();
    	$result = array();
    		    	
    	if ($this->userInfo['majors']>0) {
    		$tmp = Better_DAO_Poi_Major::getInstance()->somebody($this->uid, 1, 10);
    		
	    	if (is_array($tmp) && count($tmp)>0) {
	    		foreach ($tmp as $row) {
	    			$row['poi_name'] = $row['name'];
	    			$row['major_time'] = $row['major_change_time'];
	    			
	    			$result[] = $row;
	    		}
	    	}
    	}
    	
    	return $result;    	
    }
    
    /**
     * 获取用户是哪些POI的皇帝
     * 
     * @param $page
     * @param $count
     * @return array
     */
    public function &getAll($page=1, $count=BETTER_PAGE_SIZE)
    {
    	$this->getUserInfo();
    	$result = array(
    		'total' => 0,
    		'rows' => array(),
    		);
    		    	
    	if ($this->userInfo['majors']>0) {
    		
    		$tmp = Better_DAO_Poi_Search::getInstance()->search(array(
	    		'major' => $this->uid,
	    		'page' => $page,
	    		'count' => $count,
	    		'order' => 'major_change_time',
	    		));

	    	$result = array(
	    		'total' => $tmp['total'],
	    		'rows' => array(),
	    		'pages' => Better_Functions::calPages($tmp['total'], $count)
	    		);
	
	    	if ($tmp['total']>0) {
	    		foreach ($tmp['rows'] as $row) {
	    			$poiInfo = Better_Poi_Info::createInstance($row)->getBasic();
	    			$row['poi_name'] = $poiInfo['name'];
	    			$row['major_time'] = $poiInfo['major_change_time'];
	    			$row['logo_url'] = $poiInfo['logo_url'];
	    			
	    			$result['rows'][] = $row;
	    		}
		    	foreach ($result['rows'] as $key => $value) {
					$time[] = $value['major_change_time'];
					$poiid[$key] = $value['poi_id'];
				}
					
	    		array_multisort($time, $poiid, $result['rows']); 
	    		$tt = array_reverse($result['rows']);
	    		$result['rows'] = &$tt;
	    		unset($tt);
	    	}
    	}
    	
    	return $result;
    }
    
    /**
     * 记录用户成为皇帝
     * 
     * @return bool
     */
    public function log($poiId)
    {
    	$result = false;
    	
    	$this->delta = $poiId;
    	
    	$userInfo = $this->user->getUserInfo();
    	
    	$this->user->updateUser(array(
    		'majors' => $userInfo['majors']+1,
    		));
    		
    	$poi = Better_Poi_Info::getInstance($poiId);

    	$poi->update(array(
    		'major' => $this->uid,
    		'major_change_time' => time(),
    		));

    	Better_DAO_User_MajorLog::getInstance($this->uid)->insert(array(
    		'uid' => $this->uid,
    		'poi_id' => $poiId,
    		'dateline' => time(),
    		));
    		
    	if ($this->user->isPublic()) {	
	    	Better_Hook::factory(array(
	    		'Syncsites',
	    		))->invoke('GetMajor', array(
	    		'uid' => $this->uid,
	    		'poi_id' => $poiId
	    		));
    	}
    	
    	$cacher = Better_Cache::remote();
    	$cacheKey = 'user_majors_'.$this->uid;
    	$cacher->set($cacheKey, null);
    	
    	$result = true;

    	return $result;
    }
    
    /**
     * 获取用户的掌门历史
     * 
     * @param $page
     * @param $count
     * @return array
     */
    public function &getHistory($page=1, $count=BETTER_PAGE_SIZE)
    {
    	$this->getUserInfo();
    	
		$return = array(
			'rows' => array(),
			'count' => 0,
			'pages' => 0,
			);

		$sessUid = Better_Registry::get('sess')->get('uid');
		
		//if ($sessUid==$this->uid || ($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($this->userInfo['priv']=='protected' && $this->user->isFriend($sessUid))))) {
			$return = Better_DAO_User_MajorLog::getInstance($this->uid)->getMyMajorLog($page, $count);			
		//}
		
		return $return;
    }
    
    /**
     * 计算在某个poi的掌门权重
     * 
     * @return integer
     */
    public function calMajorWeight($poiId, $validCount=false, $day=0)
    {
    	if ($validCount===false) {
    		if ($day) {
    			$validCount = Better_DAO_User_PlaceLog::getInstance($this->uid)->getCheckinCountByDay($poiId, $day, true);
    		} else {
    			$validCount = Better_DAO_User_PlaceLog::getInstance($this->uid)->getTowMonthCheckinCount($poiId, true);
    		}
    	} 
    	
    	//$invalidCount = Better_DAO_User_PlaceLog::getInstance($this->uid)->getTowMonthCheckinCount($poiId, false);
    	$invalidCount = 0;
    	return (int)($validCount*2 + $invalidCount);
    }
}