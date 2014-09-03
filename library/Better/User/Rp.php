<?php

/**
 * 
 * 用户Rp值
 * 
 * @package Better.User
 * @author leip
 *
 */
class Better_User_Rp extends Better_User_Base
{
	private static $instance = array();
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
     * 更新
     * 
     * @return bool
     */
    public function update(array $params)
    {
    	$rp = $params['rp'];
    	$poiId = $params['poi_id'];
    	
    	$userInfo = $this->user->getUserInfo();
    	$updated = false;
    	Better_Log::getInstance()->logInfo(serialize($userInfo),'rp');
 	
    	if ($rp!=0) {
    		$this->delta += (float)$rp;
    		if($rp<0 && $userInfo['rp']<=abs($rp)){
    			$rp = -$userInfo['rp'];
    		}
    		$newRp = $userInfo['rp'] + $rp;
    		
    		Better_Log::getInstance()->logInfo($newRp,'rp');
	    	$this->user->updateUser(array(
	    		'rp' => $newRp,
	    		), true);
	    	
	    	$this->log(array(
	    		'rp' => $rp,
	    		'category' => $params['category'],
	    		'co_uid' => isset($params['co_uid']) ? (int)$params['co_uid'] : 0,
	    		'note' => isset($params['note']) ? $params['note'] : '',
	    		));
	    	$live_city = mb_substr($userInfo['live_city'], 0, 3, 'UTF-8');
	    	$live_city && Better_DAO_Rp::getInstance()->updateRp($this->uid, $rp, $live_city);	
	    	$updated = true;
	    	Better_Hook::factory(array(
	    		'Badge'
	    	))->invoke('RpChange', array(
	    		'uid' => $this->uid,
	    		'orig' => $rp,
	    		'new' => $newRp,
	    		'poi_id' => $poiId,
	    	));
    	}
    	
    	return $updated;
    }
    
    /**
     * 计算某个事件的Karma变化
     */
    public function calculate($event, array $params=array())
    {
    	$rp = 0;
    	
    	try {
    		$calculator = Better_User_Rp_Calculator::getInstance($this->uid);
    		$rp = call_user_func(array(
					$calculator, 
					'on'.ucfirst($event)
					), $params);
    	} catch (Exception $e) {
    		Better_Log::getInstance()->logAlert('Rp calculate failed', 'rp');
    	}
    	
    	return $rp;
    }
    
    public function cangetnativedaytotalrp(){
    	$rp = 0;
    	$rp = Better_DAO_RpLog::getInstance($this->uid)->cangetNativedayTotalrp();
    	return $rp;
    }
    
    /**
     * 记录变化日志
     * 
     * @param $params
     */
    public function log(array $params)
    {
    	$note = isset($params['note']) ? $params['note'] : '';
    	$coUid = isset($params['co_uid']) ? (int)$params['co_uid'] : 0;
    	$category = isset($params['category']) ? $params['category'] : 'unknown';
    	$rp = (float)$params['rp'];
    	
    	$this->getUserInfo();
    	$rpNow = $this->userInfo['rp'];    	
    	if ($rp!=0) {
	    	Better_DAO_User_RpLog::getInstance($this->uid)->insert(array(
	    		'uid' => $this->uid,
	    		'dateline' => time(),
	    		'note' => $note,
	    		'category' => $category,
	    		'co_uid' => $coUid,
	    		'rp_before' => $rpNow,
	    		'rp' => $rp
	    		));
    	}
    }
}