<?php

/**
 * 用户勋章
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Diybadge extends Better_User_Base
{
	
	
	
	
	public function __construct(array $params=array())
	{
		$this->params = $params;
	}
	
	
	public function is_where($poilist){
		$params = $this->params;
		$result = false;	
		$poi_id = $params['poi_id'];	
		$result = in_array($poi_id,$poilist) ? true : false;
		//var_dump("Checkin".$result."\n");		
		return $result;
	}
	
	public function had_specialsync($sync){
		$params = $this->params;
		$uid = $params['uid'];
		$synclist = Better_User_Syncsites::getInstance($uid)->getSites();		
		$result = false;
		foreach($sync as $row){
			$result = isset($synclist[$row]);
			if(!$result){
				break;
			}
		}
		if($result){
			$result = true;
		} else {
			$result = false;
		}
		//var_dump("specialsync".$result."\n");
		return $result;
	}
	public function had_syncs($nums){	
		$params = $this->params;
		$result = false;
		$uid = $params['uid'];		
		$synclist = Better_User_Syncsites::getInstance($uid)->getSites();				
		$syncnums = count($synclist);
		$result = $syncnums>=$nums ? true : false;
		//var_dump("syncs".$result."\n");
		return $result;
	}
	
	public function had_text($search){
		$params = $this->params;
		$result = false;	
		$blog = &$params['blog'];		
		$message = strtolower($blog['message']);	
	
		foreach($search as $row){	
					
			$result = preg_match(strtolower($row), $message);
			
			if($result){
				$result = true;	
				break;
			}		
		}
		
		//var_dump("text".$result."\n");
		return $result;
	}
	public function blog_type($types){
		$params = $this->params;
		$result = false;	
		$blog = &$params['blog'];
		$type = $blog['type'];
		$result = in_array($type,$types) ? true : false;
		//var_dump("type".$result."\n");		
		return $result;		
	}
	public function dis_range($range){
		$params = $this->params;
		$result = false;	
		Better_Log::getInstance()->logInfo($range."--".$params['distance'],'rangebadge');
		$result = $range>=$params['distance'] ? true:false;				
		return $result;		
	}
	public function poi_name($search){
		$params = $this->params;
		$result = false;	
		$poiId = $params['poi_id'];	
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		$poiName = strtolower($poiInfo['name']);	
		foreach($search as $row){						
			$result = preg_match(strtolower($row), $poiName);
			if($result){
				$result = true;	
				break;
			}		
		}
		return $result;
	}
	public function user_gender($gender){
		$params = $this->params;
		$result = false;
		$uid = $params['uid'];
		$userinfo = Better_User::getInstance($uid)->getUserInfo();
		$result = in_array($userinfo['gender'],$gender) ? true : false;		
		return $result;
	}
}