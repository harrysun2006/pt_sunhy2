<?php

/**
 * POI促销
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Business_Venue
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
		
		$data = array(
				'poi_id' => $params['poi_id'],
				'uid' => $params['uid'],				
				'company' => $params['company'],
				'ownername' => $params['ownername'],
				'phone' => $params['phone'],				
				'email' => $params['email'],			
				'lotsshop' =>$params['lotsshop'],
				'ownertype' =>$params['ownertype'],
				'status' =>$params['status'],
				'dateline' =>time()
		);
		
		$flag = Better_DAO_Business_Venue::getInstance()->insert($data);
		if ($flag) {			
			$code = $codes['SUCCESS'];
			$params = array(
				'poi_id' => $params['poi_id'],
				'uid' => $params['uid']
			);
			$tempinfo = Better_DAO_Business_Venue::getInstance()->getVenue($params);
			$result['r_id'] = $tempinfo['id'];			
			$emailstr = Better_Config::getAppConfig()->venue->remind->emaillist;
			$emaillist = split(",",$emailstr);								
			$mailuid = 10000;
			$poiInfo = Better_Poi_Info::getInstance($params['poi_id'])->getBasic();
			$lang = Better_Language::loadIt('zh-cn');
			$title = $lang->global->venue_remind->title;			
			$content = $lang->global->venue_remind->content;
			$template = $lang->global->venue_remind->template;								
			$user = Better_User::getInstance($params['uid']);
			$userinfo = $user->getUserInfo();			
			$content = str_replace("{SOMEBODY}",$userinfo['nickname'],$content);
			$content = str_replace("{TIMES}",date('Y-m-d H:i:s', time()+BETTER_8HOURS),$content);
			$content = str_replace("{POINAME}",$poiInfo['name'],$content);
			$content = str_replace("{COMPANYNAME}",$data['company'],$content);
			$content = str_replace("{OWNERNAME}",$data['ownername'],$content);
			$content = str_replace("{PHONE}",$data['phone'],$content);
			$content = str_replace("{EMAIL}",$data['email'],$content);
			$content = str_replace("{POIID}",$params['poi_id'],$content);
		
			try{				
				foreach($emaillist as $row){
					$email = $row;					
					$mailer = new Better_Email($mailuid);
					$mailer->setSubject($title);
					$mailer->setTemplate($template);		
					$mailer->addReceiver($email, $email);					
					$mailer->set(array(
						'CONTENT' => $content
						));					
					$mailer->send2();
				} 				
			}catch(Exception $bb){
					Better_Log::getInstance()->logInfo($tempinfo['id'],'mailremindfalse');
			}			
		}
		
		$result['code'] = $code;
		
		return $result;
	}
	
	public function getVenue(array $params)
	{
		$result = array();		
		$data = array(
			'id' => $params['id'],
			'poi_id' => $params['poi_id'],
			'uid' => $params['uid']
		);
		$code = 0;
		$tempinfo = Better_DAO_Business_Venue::getInstance()->getVenue($data);
		if($tempinfo['id']>0){
			$code = 1;
			$result['venue'] = $tempinfo;
		}	
		$result['code'] = $code;		
		
		return $result;
	}
}