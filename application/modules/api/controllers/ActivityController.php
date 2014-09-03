<?php

/**
 * 活动 API
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_ActivityController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		$this->auth();
	}		
	
	/**
	 * 获得活动列表
	 */
	public function activitiesAction()
	{
		$this->xmlRoot = 'activities';
		
		$closed = (string)$this->getRequest()->getParam('closed', 'false');
		
		$activities = Better_DAO_Activity::getInstance()->getActivities(array(
					'closed' => $closed == 'true',
					'page' => $this->page,
					'count' => $this->count,
				));
		foreach ($activities as $activity) {
			$data[] = array(
				'activity' => $this->api->getTranslator('activity')->translate(array(
					'data' => &$activity,
				)),
			);
		}
		
		$this->data[$this->xmlRoot] = $data;
		$this->output();
	}
	
	public function listAction()
	{
		$this->xmlRoot = 'activities';
		
		$poi_id = (int)$this->getRequest()->getParam('poi_id', 0);
		if ($poi_id > 0) {
			$activities = Better_DAO_Activity::getInstance()->getActivitiesAtPoi(array(
					'poi_id'=>$poi_id,
					'page' => $this->page,
					'count' => $this->count,
				));
		} else {
			list($lon, $lat, $range, $accuracy) = $this->mixLL();
			$range = $this->getRequest()->getParam('range', 5000);
			$activities = Better_DAO_Activity::getInstance()->getActivitiesAround(array(
					'lon' => $lon,
					'lat' => $lat,
					'range' => $range,
					'page' => $this->page,
					'count' => $this->count,
				));
		}
		
		
		foreach ($activities as $activity) {
			$data[] = array(
				'activity' => $this->api->getTranslator('activity')->translate(array(
					'data' => &$activity,
				)),
			);
		}
		
		$this->data[$this->xmlRoot] = $data;
		$this->output();
	}
	
	/**
	 * 获得活动详情。
	 * 列表里可能只返回活动内容的一部分，后台也需要统计活动的点击量。所以需要这个API
	 */
	public function showAction()
	{
		$this->xmlRoot = 'activity';
		$act_id = (int)$this->getRequest()->getParam('id', 0);
		$browse = (string)$this->getRequest()->getParam('browse');
		$closed = (string)$this->getRequest()->getParam('closed', 'false');

		$activity = Better_DAO_Activity::getInstance()->getActivity($act_id, $browse, $closed=='true');
		if ($activity) {
			$this->data[$this->xmlRoot] = $this->api->getTranslator('activity')->translate(array(
					'data' => &$activity,
				));
		} else {
			$this->error('error.activity.invalid_id');
		}
		$this->output();
	}
 
 	/**
 	 * 获得活动关联的POI列表
 	 */
 	public function poisAction()
 	{
 		$this->xmlRoot = 'pois';
	
		$act_id = (int)$this->getRequest()->getParam('id', 0);
		$smallIcon = (bool)($this->getrequest()->getParam('small_icon', 'false')=='true' ? true : false);

		$pois = Better_DAO_Activity::getInstance()->getAttachedPois($act_id);
		foreach ($pois as $row) {
			$this->data[$this->xmlRoot][] = array(
				'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					'small_icon' => $smallIcon,
					)),
				);
		}
		$this->output();
 	}
}