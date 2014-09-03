<?php

/**
789艺文节
吼吼/签到/贴士中含有“789艺文节”字样
即时
2011年9月7日24:00


 *
 */
class Better_DAO_Badge_Calculator_Qibajiuywj extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(1, 0, 0, 8, 25, 2011);
		$endtm = gmmktime(16, 0, 0, 9, 7, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm){	
			$blog = &$params['blog'];							
			if ($blog['type']!='todo') {			
				$message = strtolower($blog['message']);	
				$search = array('/789艺文节/');
				foreach($search as $row){							
					$result = preg_match(strtolower($row), $message);					
					if($result){
						$result = true;	
						break;
					}		
				}				
			}							
		}
		return $result;
	}
}