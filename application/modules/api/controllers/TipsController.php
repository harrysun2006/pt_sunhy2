<?php

/**
 * 贴士
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_TipsController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		
		$this->xmlRoot = 'tips_space';
		
		$this->auth();
	}	
	
	/**
	 * 
	 * 贴士的公共空间
	 */
	public function publictimelineAction()
	{
		$this->xmlRoot = 'tips_space';
		
		list($lon, $lat, $range) = $this->mixLL();
		$friendsNearBy = (bool)($this->getRequest()->getParam('friends_nearby', 'false')=='true' ? true : false);
		$coupons = (bool)($this->getRequest()->getParam('coupons', 'false')=='true' ? true : false);
		$tips = (bool)($this->getRequest()->getParam('tips', 'false')=='true' ? true : false);
		$recommends = (bool)($this->getRequest()->getParam('recommends', 'false')=='true' ? true : false);
		$polo = (bool)($this->getRequest()->getParam('polo', 'false')=='true' ? true : false);
		$range = (int)$this->getRequest()->getParam('range', 5000);
		$around = (bool)($this->getRequest()->getParam('around', 'false')=='true' ? true : false);
		$around_count = (int)$this->getRequest()->getParam('around_count', 1);
		$activity = (bool)($this->getRequest()->getParam('activity', 'false')=='true' ? true : false);
		$activity_count = (int)$this->getRequest()->getParam('activity_count', 1);
		$activity_lastid = (int)$this->getRequest()->getParam('activity_lastid', 0);	
		$output = array();
		
		// 附近活动
		if ($activity) {
			$activities = Better_DAO_Activity::getInstance()->getActivitiesAroundRandom(array(
					'lon' => $lon,
					'lat' => $lat,
					'range' => $range,
					'page'=> 1,
					'count' => $activity_count,
					'last_id' => $activity_lastid,
					));
			foreach ($activities as $activity) {
				$output['activities'][] = array(
					'activity' => $this->api->getTranslator('activity')->translate(array(
						'data' => &$activity,
					)),
				);
			}
		}
		
		//	附件好友
		if ($friendsNearBy) {
			$output['friends_nearby'] = $this->user->friends()->nearByCount($lon, $lat, $range);
		}
		
		//	附近优惠
		if ($coupons) {
			$output['coupons'] = array();
			
				$rows = Better_Poi_Notification::search(array(
					'lon' => $lon,
					'lat' => $lat,
					'range' => $range,
					'page'=> $this->page,
					'count' => $this->count
					));
			
			
			$output['coupons_count'] = $rows['total'];
			foreach ($rows['rows'] as $row) {
				if ($polo) {
					$row['name'] .= '(polo)';	
				}
				
				if ($row['image_url']) {
					list($a, $b) = explode('.', $row['image_url']);
					if (is_numeric($a) && is_numeric($b)) {
						$attach = Better_Attachment_Parse::getInstance($row['image_url'])->result();
						$row['image_url'] = $attach['url'];
					}
				}
								
				$output['coupons'][] = array(
					'coupon' => $this->api->getTranslator('coupon')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}			
		}
		
		//	附近推荐
		if ($recommends) {
			$rTips = Better_Poi_Tips::recommends(array(
				'uid' => $this->uid,
				'page' => $this->page,
				'page_size' => $this->count,
				'lon' => $lon, 
				'lat' => $lat,
				'range' => $range
				));
				
			$output['recommends'] = array();
			foreach ($rTips['rows'] as $row) {
				$output['recommends'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						)),
					);
			}			
		}
		
		//	附近贴士
		if ($tips) {
			$rows = $this->user->blog()->getRangedTips(array(
				'lon' => $lon, 
				'lat' => $lat,
				'range' => $range,
				'page' => $this->page,
				'count' => $this->count
				));

			$output['tips'] = array();
			foreach ($rows['rows'] as $row) {
				$output['tips'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}								
		}
		
		
		//更多关联数据, 数据汇总
		if($around){
			$types = array(
				'tuangou'=>array('今日团购', '团购'), 
				
			);
			
			foreach($types as $what=>$conf){
				$tmp = array();
				
				$result = Better_DAO_Roundmore_Factory::create($what)->getAllMsg(array(
					'lon'=> $lon,
					'lat'=> $lat,
					'range'=> $range,
					'page'=> 1,
					'count'=> $around_count
				));
				
				
				if(count($result['rows'])>0){
					foreach($result['rows'] as $row){
						$tmp[] = array(
							'poiext'=>$this->api->getTranslator('around_common')->translate(array(
									'data' => &$row,
									'type' => $what,
									'label' => $conf[1]
									))
							);
					}
				}else{
					$tmp = array();
				}
				
				
				$output['around'][] = array(
						'item'=>array(
									'count'=>$result['total'],
									'title'=> $conf[0],
									'label'=> $conf[1],
									'type'=> $what,
									'poiexts'=>$tmp
					 			 ));
				
			}
			
		}
		
		$this->data[$this->xmlRoot] = &$output;
		$this->output();
	}
	
}
