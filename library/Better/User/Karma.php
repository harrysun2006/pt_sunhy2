<?php

/**
 * 
 * 用户Karma值
 * 
 * @package Better.User
 * @author leip
 *
 */
class Better_User_Karma extends Better_User_Base
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
    	$karma = $params['karma'];
    	$poiId = $params['poi_id'];
    	
    	$userInfo = $this->user->getUserInfo();
    	$updated = false;
    	
    	if ($karma!=0) {
    		$this->delta += (float)$karma;
    		$newKarma = $userInfo['karma'] + $karma;
	    	$this->user->updateUser(array(
	    		'karma' => $newKarma,
	    		), true);
	    	
	    	$this->log(array(
	    		'karma' => $karma,
	    		'category' => $params['category'],
	    		'co_uid' => isset($params['co_uid']) ? (int)$params['co_uid'] : 0,
	    		'note' => isset($params['note']) ? $params['note'] : '',
	    		));
	    		
	    	$updated = true;
	    	
	    	Better_Hook::factory(array(
	    		'Badge', 'DirectMessage'
	    	))->invoke('KarmaChange', array(
	    		'uid' => $this->uid,
	    		'orig' => $karma,
	    		'new' => $newKarma,
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
    	$karma = 0;
    	
    	try {
    		$calculator = Better_User_Karma_Calculator::getInstance($this->uid);
    		$karma = call_user_func(array(
					$calculator, 
					'on'.ucfirst($event)
					), $params);
    	} catch (Exception $e) {
    		Better_Log::getInstance()->logAlert('Karma calculate failed', 'karma');
    	}
    	
    	return $karma;
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
    	$karma = (float)$params['karma'];
    	
    	$this->getUserInfo();
    	$karmaNow = $this->userInfo['karma'];
    	
    	if ($karma!=0) {
	    	Better_DAO_User_KarmaLog::getInstance($this->uid)->insert(array(
	    		'uid' => $this->uid,
	    		'dateline' => time(),
	    		'note' => $note,
	    		'category' => $category,
	    		'co_uid' => $coUid,
	    		'karma_before' => $karmaNow,
	    		'karma' => $karma
	    		));
    	}
    }
}