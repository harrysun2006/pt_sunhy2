<?php

/**
 * 商家认证
 *
 * @package Controllers
 * @author yangl
 */

class VenueController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
		//$this->needLogin();
    	$this->commonMeta();
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/venue.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
		$this->view->myfollowing = $this->uid ? $this->user->follow()->getFollowings() : array();
		$this->view->myblocking = $this->uid ? Better_User_Block::getInstance($this->uid)->getBlocks() : array();

		$this->view->headScript()->prependScript('
		betterUser.blocks = '.Better_Functions::toJsArray($this->view->myblocking).';'
		);
   		
   		
   		$this->view->shout_title = $this->lang->global->tips->title;
   		$this->view->shout_type = 'tips';
   		$this->view->shout_text = $this->lang->global->tips->text;

   		$this->view->needCheckinJs = true;
	}
	
	public function __call($method, $params)
	{
		$params = $this->getRequest()->getParams();
		$poiId = $params['action'];
		$this->indexAction($poiId);
		$this->render('index');
	}	
	
	public function indexAction()
	{
		$poiId = $poiId ? $poiId : Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));		
		$_SESSION['venue_end_url'] = $_SERVER['HTTP_REFERER'];		
		if($poiId>0){
			$this->view->poi_id = $poiId;
		}
	}	
	
	public function step1Action(){		
		$poiId = $poiId ? $poiId : Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));
		if($poiId>0){
			$this->view->poi_id = $poiId;
			$poiInfo = Better_Poi_Info::getInstance($poiId);
			$poiDetail = $poiInfo->get();
			$this->view->poiInfo = $poiDetail;			
			$this->view->headScript()->prependScript('
			var Better_Poi_Id =\''.$poiDetail['poi_id'].'\';
			var Better_Poi_Detail = '.json_encode($poiDetail).';
			pageLon = '.(float)$poiDetail['lon'].';
			pageLat = '.(float)$poiDetail['lat'].';
			var Better_Poi_Favorited = '.(int)$this->view->favorited.';
				');
			
			
		}
		
	}
	public function postrequireAction(){
		
		$params = $this->getRequest()->getPost();	
		
		$return = array('has_err'=>1);
		if($params['poi_id']>0){	
			$data['poi_id'] = $params['poi_id'];
			$data['company'] = $params['company'];
			$data['ownername'] = $params['ownername'];
			$data['phone'] = $params['phone'];
			$data['email'] = $params['email'];
			$data['uid'] = $this->uid;
			$data['lotsshop']= isset($params['hadlotspoi'])?$params['hadlotspoi']:0;
			$data['ownertype']= isset($params['ownertype'])?$params['ownertype']:0;
			$data['status']= 0;
			$data['dateline'] = time();
			$checkinfo =  Better_Business_Venue::getVenue($data);			
			if($checkinfo['code']==0 && ($poiinfo['ownerid']==0 || $poiinfo['ownerid']==''))	{
				$result = Better_Business_Venue::create($data);			
				if($result['code']==$result['codes']['SUCCESS']){
					$return['has_err'] =0;
					$return['r_id'] = $result['r_id'];
					$return['poi_id'] =$data['poi_id'];
				}	
			} else {
				$return['has_err'] = 2;
			}
		}
		echo json_encode($return);
		exit(0);
	}
	public function step2Action(){	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/venue/notification.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));	
   			
		$this->view->poi_id = Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));
		$this->view->r_id = Better_Poi_Info::dehashId($this->getRequest()->getParam('r_id', ''));
		$this->view->gotourl = Better_Config::getAppConfig()->base_url."/venue/step3?id=".$this->view->poi_id;
		if((int)$this->view->poi_id==0 || (int)$this->view->r_id==0){
			$url = Better_Config::getAppConfig()->base_url."/venue/step1";
			$this->_helper->getHelper('Redirector')->gotoUrl($url);
			exit(0);
		}
	}
	
	public function postnotificationAction(){
		$params = $this->getRequest()->getPost();		
		$return = array('has_err'=>1);
		if($params['poi_id']>0){	
			$data['poi_id'] = $params['poi_id'];
			$data['title'] = '';
			$data['content'] = $params['message'];
			$data['image_url'] = $params['attach'];		
			$data['creator'] = $this->uid;
			$data['begintm']= $params['begintm'];
			$data['endtm']= $params['endtm'];
			$data['r_id']= $params['r_id'];			
			$result = Better_Business_Notification::create($data);			
			if($result['code']==$result['codes']['SUCCESS']){
				$return['has_err'] =0;
				$return['poi_id'] =$data['poi_id'];
			}	
		}
		echo json_encode($return);
		exit(0);
	}
	
	public function step3Action(){	
		$poi_id = Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));
		$poiurl = "/poi/".$poi_id;		
		$this->view->gotourl = 	isset($_SESSION['venue_end_url'])? $_SESSION['venue_end_url']:$poiurl;
		$_SESSION['venue_end_url'] = '';
		$this->view->poi_id = $poi_id;
	}
}