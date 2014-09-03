<?php

class Better_User_Ranking extends Better_User_Base
{
	private static $instance = array();

    public static function getInstance($uid=0)
    {
    	$uid=='' && $uid=0;
        if (!array_key_exists($uid, self::$instance)) {
            self::$instance[$uid] = new self($uid);
        }
        
        return self::$instance[$uid];
    }		
    

	/**
	 * 
	 * @param array $params
	 * @return unknown_type
	 */
    public function &karmaWeekMyFriends(array $params=array())
    {
    	$page = (int)$params['page'];
    	$count = (int)$params['page_size'];
    	$page || $page = 1;
    	$count || $count = BETTER_PAGE_SIZE;
    	
		$ids = $this->user->friends;
		array_push($ids, $this->uid);	
			
    	$tmp = Better_DAO_User_Ranking::getInstance($this->uid)->karmaWeekMyFriend(array(
    		'page' => $page,
    		'page_size' => $count,
    		'ids' => $ids,
    		'uid' => $this->uid,
    		));
    		
    	$result = array();
    	$i = 0;
    	foreach ($tmp['rows'] as $row) {
    		$result['rows'][$i] = $this->user->parseUser($row);
    		$result['rows'][$i]['rp'] = $row['rp'];
    		$i++;    		
    	}
    	
    	$result['count'] = $tmp['count'];
    		
    	return $result;	     	
    }
    
    
    
    /**
     * 每周排行 城市 
     */
    public function karmaWeekMyCity($params)
    {
     	$page = (int)$params['page'];
    	$count = (int)$params['page_size'];
    	$page || $page = 1;
    	$count || $count = BETTER_PAGE_SIZE;
    	$this->getUserInfo();
    	$city = $this->userInfo['live_city']; 	
    	
    	$tmp = Better_DAO_User_Ranking::getInstance($this->uid)->karmaWeekMyCity(array(
    		'page' => $page,
    		'page_size' => $count,
    		'city' => $city,
    		));
    		
    	$result = array(
			    		'rows' => array(),
			    		'count' => 0
			    		); 
			    		
    	$i = 0;
    	foreach ($tmp['rows'] as $row) {
    		$result['rows'][$i] = $this->user->parseUser($row);
    		$result['rows'][$i]['rp'] = $row['rp'];
    		$i++;
    	}
    	
    	$result['count'] = $tmp['count'];
    		
    	return $result;	    	
    	
    }
    

	/**
	 * 每周排行 全局
	 */
	public function karmaWeekGlobal($params)
	{
    	$page = (int)$params['page'];
    	$count = (int)$params['page_size'];
    	$page || $page = 1;
    	$count || $count = BETTER_PAGE_SIZE;

    	$result = array(
    		'rows' => array(),
    		'count' => 0
    		);    	
    		
    	$tmp = Better_DAO_User_Ranking::getInstance($this->uid)->karmaWeekGlobal(array(
    		'page' => $page,
    		'page_size' => $count
    		));
    		
    	$result = array();
    	$i = 0;
    	foreach ($tmp['rows'] as $row) {
    		$result['rows'][$i] = $this->user->parseUser($row);
    		$result['rows'][$i]['rp'] = $row['rp'];
    		$i++;
    	}  	
    	$result['count'] = $tmp['count'];
    	return $result;		
	}   

	
	/**
	 * 
	 * @param array $params
	 * @return unknown_type
	 */
    public function &karmaMyFriends(array $params=array())
    {
    	$page = (int)$params['page'];
    	$count = (int)$params['page_size'];
    	$page || $page = 1;
    	$count || $count = BETTER_PAGE_SIZE;
    	
    	$result = $this->user->friends()->all($page, $count, 'p.rp DESC', true);
    	
    	return $result;
    }	
	
	
    /**
     * 
     * @param $params
     * @return unknown_type
     */
    public function &karmaMyCity(array $params=array())
    {
    	$page = (int)$params['page'];
    	$count = (int)$params['page_size'];
    	$page || $page = 1;
    	$count || $count = BETTER_PAGE_SIZE;
    	
    	$this->getUserInfo();

    	$city = $this->userInfo['live_city'];
    	
    	$result = array(
    		'rows' => array(),
    		'count' => 0
    		);    	
    		
    	if ($city) {
    		$cacher = Better_Cache::remote();
    		$cacheKey = 'karma_ranking_'.md5($city).'_'.$page.'_'.$count;
    		$result = $cacher->get($cacheKey);
    		$result = null;
    		if (!$result) {
    			$tmp = Better_DAO_User_Ranking::getInstance($this->uid)->karmaMyCity(array(
    				'city' => $city,
    				'page' => $page,
    				'page_size' => $count
    				));

    			foreach ($tmp['rows'] as $row) {
    				$result['rows'][] = $this->user->parseUser($row);
    			}
    			$result['count'] = $tmp['count'];

    			$cacher->set($cacheKey, $result, 3600*8);
    		}
    	}
    		
    	return $result;
    }
    
	

	
	/**
	 * 
	 * @param array $params
	 * @return unknown_type
	 */
    public function &karmaGlobal(array $params=array())
    {
    	$page = (int)$params['page'];
    	$count = (int)$params['page_size'];
    	$page || $page = 1;
    	$count || $count = BETTER_PAGE_SIZE;

    	$result = array(
    		'rows' => array(),
    		'count' => 0
    		);    	
    		
    	$cacher = Better_Cache::remote();
    	$cacheKey = 'karma_ranking_global_'.$page.'_'.$count;
    	$result = $cacher->get($cacheKey);
    	$result = null;
    	if (!$result) {
    		$tmp = Better_DAO_User_Ranking::getInstance($this->uid)->karmaGlobal(array(
    			'page' => $page,
    			'page_size' => $count
    			));
    			
    		$result = array();
    		foreach ($tmp['rows'] as $row) {
    			$result['rows'][] = $this->user->parseUser($row);
    		}
    		
    		$result['count'] = $tmp['count'];
    		
    		$cacher->set($cacheKey, $result, 3600*8);
    	}
    		
    	return $result;
    }
}
