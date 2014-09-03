<?php

/**
勋章名称
 地球一小时
 
获得条件
 在签到的同时吼出“地球一小时”或者吼吼中（有无勾选地点都可）含有“地球一小时”关键词可以获得。
 
上线时间
 即时
 
下线时间
 3月27日0:00 

 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Diqiuyixiaoshi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$uid = (int)$params['uid'];
		$poiId = (int)$params['poi_id'];
		$end = gmmktime(16, 0, 0, 3, 26, 2011);
		$now = time();		
		if ($now<=$end) {
			$blog = &$params['blog'];
			if($blog['type']=='checkin' || $blog['type']=='normal'){
				$message = $blog['message'];				
				if (preg_match('/地球一小时/', $message)) {
					$result = true;
				}
			}			
		}		
		return $result;
	}
}