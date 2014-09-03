<?php

/**

 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Kaiyizhousui extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 8, 21, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 28, 2011);	
		$now = time();		
		if($now>=$begtm && $now<=$endtm){	
			$blog = &$params['blog'];							
			if ($blog['type']!='todo') {			
				$result = true;					
			}								
		}
		return $result;
	}
}