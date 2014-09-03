<?php

/**
 * 宝物相关
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Mafcard
{
	protected static $instance = array();
	protected $id = 0;
	protected $info = array();
	
	protected function __construct($id)
	{
		$this->id = $id;	
	}
	
	public static function getInstance($id)
	{
		if (!isset(self::$instance[$id])) {
			self::$instance[$id] = new self($id);
		}	
		
		return self::$instance[$id];
	}
	
	/*
	 * 获得当前已经发送过明信片的用户列表
	 */
	public function getMafCardUser(){
		$result =Better_DAO_Mafcard::getInstance()->getAll();
		return $result;	
	}
	public function dogetMafCard($params){
		$havereg = Better_Mafcard::getMafCardUser();
    	$minecard = Better_Mafcard::getMyMafCard($params['uid']);
    	//Zend_Debug::dump($minecard);  
    	$user = Better_User::getInstance($params['uid']);
		$userInfo = $user->getUser();		
		$gotBadges = $user->badge()->getMyBadges();
		$gotBids = array_keys($gotBadges);		
    	$card_num = Better_Config::getAppConfig()->maf_card_num;
		$endtime = Better_Config::getAppConfig()->maf_time;		
		$mafcard = 0;
		if(time()>=Better_Config::getAppConfig()->maf_time){
			$mafcard = 1;		//超过活动时间	
		} else if(count($havereg)>=$card_num){
			$mafcard = 2;		//发放完毕	
		} else if(count($minecard)>0){
			$mafcard = 3;		//已经拥有了
		} else if(in_array(28,$gotBids)){
			$mafcard = 4;		//可以拥有
		} else {
			$mafcard = 5;      //不可以拥有
		}
		$result = 0;
		
		if($mafcard==4){
			$result = Better_Dao_Mafcard::getInstance()->insert(
				array(
					"uid" => $params['uid'],
					"receive_name" => $params['receive_name'],
					"receive_address" => $params['receive_address'],
					"receive_zipcode" => $params['receive_zipcode'],
					"post_name" => $params['post_name'],
					"post_address" => $params['post_address'],
					"post_zipcode" => $params['post_zipcode'],
					"message" => $params['message'],
					"dateline" => time()		
			));
		}
		$date = array(
			"mafcard" => $mafcard,
			"result" => $result
		);			
		return $date;
	}
	public function getMyMafCard($uid){
		$result =Better_DAO_Mafcard::getInstance()->getMine($uid);
		return $result;
	}
	
}