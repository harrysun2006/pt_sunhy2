<?php

/**
 * POI促销
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Notification extends Better_Poi_Base
{
	protected static $instance = array();
	
	protected function __construct($poiId)
	{
		parent::__construct($poiId);
	}
	
	public static function getInstance($poiId)
	{
		if (!isset(self::$instance[$poiId])) {
			self::$instance[$poiId] = new self($poiId);
		}
		
		return self::$instance[$poiId];
	}	
	
	
	public function &getAll($page=1, $count=BETTER_PAGE_SIZE, $platform=0)
	{		
		if(Better_Config::getAppConfig()->market->wlan->switch){		
			$wlanpoi = Better_Market_Cmcc::getInstance()->poilist();
			$poilist = array();
			foreach($wlanpoi as $row){
				foreach($row as $rows){
					$poilist[]= $rows;
				}	
			}		
			$params['poi_id'] = $this->poiId;
		}
		if(Better_Config::getAppConfig()->market->wlan->switch && in_array($this->poiId,$poilist)){
			$newdate = array();
			$datepolo = Better_DAO_Poi_Notificationpolo::getInstance()->getPoispecial($params);
			$datenomal =  Better_DAO_Poi_Notification::getInstance()->getPoispecial($params);
			if(is_array($datenomal) && count($datenomal['rows']>0)){
				foreach($datenomal['rows'] as $row){
					$row['nid'] = $row['nid'];
					$newdate[] = $row;	
				}
			}
			if(is_array($datepolo) && count($datepolo['rows']>0)){				
			 	foreach($datepolo['rows'] as $row){
					$row['nid'] = 100000+$row['nid'];
					$row['id'] = $row['nid'];
					$_url = str_replace("|","%7c",$row['image_url']);
					if ($platform == '1') {
						$_len = strlen($_url);
						if ($_len >= 128) {
							$_url = '';
						}
					}
					
					$row['image_url'] = $_url;
					
					$newdate[] = $row;	
				}
			}		
			
			$rows = array();
			$rows['count'] = $rows['total'] = count($newdate);		
			$rows['rows'] = &$newdate;			
		} else {
			$rows = Better_DAO_Poi_Notification::getInstance()->getCoupons(array(
			'poi_id' => $this->poiId,
			'page' => $page, 
			'page_size' => $count
			));
		}	
		return $rows;
	}
	
	public function getCheckedCount()
	{
		$nums=0;
		if(Better_Config::getAppConfig()->market->wlan->switch){		
			$wlanpoi = Better_Market_Cmcc::getInstance()->poilist();
			$poilist = array();
			foreach($wlanpoi as $row){
				foreach($row as $rows){
					$poilist[]= $rows;
				}	
			}		
			$params['poi_id'] = $this->poiId;
		}
		if(Better_Config::getAppConfig()->market->wlan->switch && in_array($this->poiId,$poilist)){
			$newdate = array();
			$datepolo = Better_DAO_Poi_Notificationpolo::getInstance()->getPoispecial($params);
			$datenomal =  Better_DAO_Poi_Notification::getInstance()->getPoispecial($params);
			$nums = count($datenomal['rows'])+count($datepolo['rows']);				
		} else {
			$nums = Better_DAO_Poi_Notification::getInstance()->getCheckedCount($this->poiId);
		}	
		
		
		
		return $nums;
	}
	
	public function getInfo(array $params){		
		$poi_id = $params['poi_id'];
		$nid =  $params['nid'];
		return Better_DAO_Poi_Notification::getInstance($poi_id)->getInfo($nid);
	}	
	
	public static function getCoupon($id)
	{
		return Better_DAO_Poi_Notification::getInstance()->getInfo($id);
	}
	
	
	public static function &search(array $params)
	{
		if ($params['polo']) {
			return Better_DAO_Poi_Notificationpolo::getInstance()->search($params);	
		} else {
			return Better_DAO_Poi_Notification::getInstance()->search($params);	
		}
		
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
		$specialToInsert = array(
				'poi_id' => $params['poi_id'],
				'uid' => $params['creator'],				
				'title' => $params['title'],
				'content' => $params['content'],
				'image_url' => $params['image'],
				'checked' => 0,	
				'dateline' => time(),	
				'begintm' => $params['begintm'],
				'endtm' => $params['endtm'],	
				'groupid' => $params['groupid']	
				);
		$nid = 	$params['nid'];	
		if($nid>0){			
			$flag = Better_DAO_Poi_Notification::getInstance()->update($specialToInsert,$nid);
		} else {
			$flag = Better_DAO_Poi_Notification::getInstance()->insert($specialToInsert);
			$nid = $flag;
		}	  

		if ($flag) {
			Better_Config::getAppConfig()->poi->fulltext->enabled  && Better_DAO_Poi_Fulltext::getInstance()->updateItem($params['poi_id'], 1);			

			$code = $codes['SUCCESS'];
		}
		
		$result['code'] = $code;
		$result['nid'] = $nid;
		
		return $result;
	}
	public static function getPoispecial(array $params)
	{		
		if(Better_Config::getAppConfig()->market->wlan->switch){		
			$wlanpoi = Better_Market_Cmcc::getInstance()->poilist();
			$poilist = array();
			foreach($wlanpoi as $row){
				foreach($row as $rows){
					$poilist[]= $rows;
				}	
			}				
		}
		if(Better_Config::getAppConfig()->market->wlan->switch && in_array($params['poi_id'],$poilist)){
			$newdate = array();
			$datepolo = Better_DAO_Poi_Notificationpolo::getInstance()->getPoispecial($params);
			$datenomal =  Better_DAO_Poi_Notification::getInstance()->getPoispecial($params);
			if(is_array($datenomal) && count($datenomal['rows']>0)){
				foreach($datenomal['rows'] as $row){
					$row['nid'] = $row['nid'];
					$newdate[] = $row;	
				}
			}
			if(is_array($datepolo) && count($datepolo['rows']>0)){
				foreach($datepolo['rows'] as $row){
					$row['nid'] = 100000+$row['nid'];
					$row['image_url'] = str_replace("|","%7c",$row['image_url']);
					$newdate[] = $row;	
				}		
			}
			$date = array();
			$date['count'] = $date['total'] = count($newdate);		
			$date['rows'] = &$newdate;			
		} else {
			$date =  Better_DAO_Poi_Notification::getInstance()->getPoispecial($params);
		}		
		$result = array();
	    foreach($date['rows'] as $row){
	    	$row['content'] = nl2br($row['content']);	    	
			if($row['image_url']){
				if(preg_match('/^([0-9]+).([0-9]+)$/', $row['image_url']))	{
					$attach = Better_Attachment_Parse::getInstance($row['image_url'])->result();
					$row['attach_tiny'] = $attach['tiny'];
					$row['attach_thumb'] = $attach['thumb'];
					$row['attach_url'] = $attach['url'];						
				} else if (preg_match('/^http(.+)$/', $row['image_url'])) {					
					$row['attach_tiny'] = $row['attach_thumb'] = $row['attach_url'] = $row['image_url'];
				}
			}		
	   		switch($row['checked']){
				case '0':
					$row['check_type'] = '未审核';
					break;
				case '1':
					$row['check_type'] = '进行中';
					break;
				case '2':
					$row['check_type'] = '审核不通过';
					break;
				case '4':
					$row['check_type'] = '用户取消';
					break;
				case '5':
					$row['check_type'] = '过期了';
					break;		
			}
			$result['rows'][]= $row;	
		}
		$result['total'] = $date['total'];
		return $result;
	}
	
	/**
	 * 得到Polo的活动
	 */
	public static function getPoloEvent()
	{		
		return Better_DAO_Poi_Notificationpolo::getInstance()->getAll(array('nid' => array(1)));
	}
	public static function getPoloCoupon($id)
	{
		$row = Better_DAO_Poi_Notificationpolo::getInstance()->getInfo($id);
		
		$row['image_url'] = str_replace("|","%7c",$row['image_url']);
		return $row;
	}
}