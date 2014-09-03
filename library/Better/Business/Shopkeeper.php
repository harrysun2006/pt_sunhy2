<?php

/**
 * POI促销
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Business_Shopkeeper
{
	protected static $instance = array();
	
	protected function __construct($poiId)
	{
		parent::__construct($poiId);
	}
	
		
	
	

	public static function createvenue(array $params)
	{
		
		$result = array();
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1
			);
		$result['codes'] = &$codes;
		$code = $codes['FAILED'];
		
		$data = array(
				'poi_id' => $params['poi_id'],
				'uid' => $params['uid'],				
				'company' => $params['company'],
				'ownername' => $params['ownername'],
				'phone' => $params['phone'],				
				'email' => $params['email'],			
				'lotshop' =>$params['lotshop'],
				'ownertype' =>$params['ownertype'],
				'status' =>$params['status'],
				'dateline' =>time()
		);
		
		$flag = Better_DAO_Business_Venue::getInstance()->insert($data);
		if ($flag) {			
			$code = $codes['SUCCESS'];
		}
		
		$result['code'] = $code;
		
		return $result;
	}
	
}