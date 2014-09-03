<?php
/**
 * admin POI controller
 * @author yangl
 */
class Admin_BadgealarmController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/badgealarm.js?ver='.BETTER_VER_CODE);
		$this->view->title="勋章报警";		
	}
	
	public function indexAction()
	{ 
		$params = $this->getRequest()->getParams();
		
		$result=Better_Admin_Badgealarm::filter($params);
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
		
		$this->view->availableBadges = Better_DAO_Badge::getInstance()->getAllAvailable();
	}
	
	
	
	public function updateAction(){
		$result=0;
		$params = $this->getRequest()->getParams();
		$xid=isset($params['xid'])? $params['xid']:'';
		$begintime=isset($params['begintime'])? strtotime($params['begintime'])-8*3600: 0;
		$endtime=isset($params['endtime'])? strtotime($params['endtime'])-8*3600: 0;
		$interval=isset($params['interval'])? $params['interval']: 0;
		
		if($xid){
			$data=array(
				'begin_time'=>$begintime,
				'end_time'=>$endtime,
				'interval'=> $interval,
			);
		}else{
			$data=array();
		}
		
		if(count($data)>0){
			Better_DAO_Admin_Badgealarm::getInstance()->update($data, $xid) && $result=1;
		}
		
		$this->sendAjaxResult($result);
		
	}
	
	
	public function deleteAction(){
		$result = 0;
		$bids = $this->getRequest()->getParam('bids', array());
		
		if(count($bids)>0){
			foreach($bids as $bid){
				Better_DAO_Admin_Badgealarm::getInstance()->delete($bid);	
			}
			$result = 1;
		}
		
		$this->sendAjaxResult($result);
	}
	
	
	public function addAction(){
		$result = 0;
		$badge_id = $this->getRequest()->getParam('bid', 0);
		$begin_time = $this->getRequest()->getParam('begin_time', 0);
		$end_time = $this->getRequest()->getParam('end_time', 0);
		$interval = $this->getRequest()->getParam('interval', 0);
		
		$badge = Better_DAO_Badge::getInstance()->getBadge($badge_id);
		if($badge){
			$badge_name = $badge['badge_name'];
			$data = array(
				'badge_id'=>$badge_id,
				'badge_name'=>$badge_name,
				'begin_time'=>strtotime($begin_time)-8*3600,
				'end_time'=>strtotime($end_time)-8*3600,
				'interval'=>$interval,
				'last_check'=>0
			);
			Better_DAO_Admin_Badgealarm::getInstance()->insert($data) && $result=1;
		}
		
		
		$this->sendAjaxResult($result);
	}
	
}

?>