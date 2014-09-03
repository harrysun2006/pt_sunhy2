<?php

/**
 * POI促销
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Business_Notification
{
	protected static $instance = array();
	
	protected function __construct($poiId)
	{
		parent::__construct($poiId);
	}
	
		
	
	

	public static function create(array $params)
	{
		
		$result = array();
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1
			);
		$result['codes'] = &$codes;
		$code = $codes['FAILED'];		
		
		$venue_data = array(
			'poi_id' => $params['poi_id'],
			'id' =>$params['r_id'],
			'uid' => $params['creator']
		);
		$checkinfo =  Better_Business_Venue::getVenue($venue_data);	
		Better_Log::getInstance()->logInfo(serialize($checkinfo),'venue');
		if($checkinfo['venue']['id']){
			$data = array(
					'poi_id' => $params['poi_id'],
					'r_id' =>$params['r_id'],
					'uid' => $params['creator'],				
					'title' => $params['title'],
					'content' => $params['content'],
					'image_url' => $params['image_url'],
					'checked' => 0,	
					'dateline' => time(),	
					'begintm' => $params['begintm'],
					'endtm' => $params['endtm'],		
					);
		
			$flag = Better_DAO_Business_Notification::getInstance()->insert($data);
		}
		if ($flag) {
			$code = $codes['SUCCESS'];
		}		
		$result['code'] = $code;		
		return $result;
	}
	
}