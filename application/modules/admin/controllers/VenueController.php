<?php
/**
 * admin POI controller
 * @author yangl
 */
class Admin_VenueController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();						
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/venue.js?ver='.BETTER_VER_CODE);
		$this->view->title="商户审核";		
	}	
	
	
	public function indexAction(){		
		$params = $this->getRequest()->getParams();		
		$result=Better_Admin_Business::getVenue($params);	
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
		$this->view->categories = $poicategories;
	}
	public function cancelspecialAction(){		
		$nid = $this->getRequest()->getParam('id', '');
		$poi_Id = $this->getRequest()->getParam('poi_id', '');
		$specialDetail = array();		
		$params = array(
			'nid'    => $nid,
			'poi_id' => $poi_Id
		);
		if ($nid>0) {
			$specialInfo = Better_Poi_Notification::getInstance($poi_Id);				
			$specialDetail =$specialInfo->getInfo($params);	
		}
		
		$this->view->headScript()->prependScript('
		var Better_Special_Id =\''.$specialDetail['uid'].'\';
		var Better_Special_Detail = '.json_encode($specialDetail).';
		');		
		$this->view->specialDetail = $specialDetail;		
	}
	public function refusevenueAction(){		
		$id = $this->getRequest()->getParam('id', '');
		$params['id'] = $id;
		$venue = Better_Business_Venue::getVenue($params);
			
		$this->view->headScript()->prependScript('
		var Better_Venue_Id =\''.$id.'\';
		var Better_Venue_Detail = '.json_encode($venue).';
		');		
		$this->view->venueDetail = $venue;		
	}
	public function checkvenueAction(){		
		$result = 0;
		$params = $this->getRequest()->getParams();		
		Better_Admin_Business::checkVenue($params) && $result=1;
		$this->sendAjaxResult($result);	
	}
	public function updatespecialAction(){
		$this->view->headScript()->appendFile($this->jsUrl.'/jquery/mixed.js?ver='.BETTER_VER_CODE);
		$nid = $this->getRequest()->getParam('id', '');
		$poi_Id = $this->getRequest()->getParam('poi_id', '');		
		$specialDetail = array();		
		$params = array(
			'nid'    => $nid,
			'poi_id' => $poi_Id
		);
		if ($nid>0) {
			$specialInfo = Better_Poi_Notification::getInstance($poi_Id);		
			$specialDetail =$specialInfo->getInfo($params);				
			if($specialDetail['image_url'])	{			
				if(preg_match('/^([0-9]+).([0-9]+)$/', $specialDetail['image_url']))	{
					$attach = Better_Attachment_Parse::getInstance($specialDetail['image_url'])->result();
					$specialDetail['attach_tiny'] = $attach['tiny'];
					$specialDetail['attach_thumb'] = $attach['thumb'];
					$specialDetail['attach_url'] = $attach['url'];	
				} else if (preg_match('/^http(.+)$/', $specialDetail['image_url'])) {
					$specialDetail['attach_tiny'] = $specialDetail['attach_thumb'] =$specialDetail['attach_url'] = $specialDetail['image_url'];
				}
			}
		}		
		$this->view->headScript()->prependScript('
		var Better_Special_Id =\''.$specialDetail['uid'].'\';
		var Better_Special_Detail = '.json_encode($specialDetail).';
		');		
		$this->view->specialDetail = $specialDetail;		
	}
	public function newspecialAction(){
		$this->view->headScript()->appendFile($this->jsUrl.'/jquery/mixed.js?ver='.BETTER_VER_CODE);
				
	}
	public function doupdatespecialAction(){
		$result = 1;
		$params = $this->getRequest()->getParams();		
		Better_Admin_Poi::updateSpecial($params) && $result=1;
		$this->sendAjaxResult($result);	
	}
	public function donewspecialAction(){
		$result = 1;
		$params = $this->getRequest()->getParams();
			
		$poi_id = (int)$params['poi_id'];
		$content = trim($params['content']);
		$begintm = (int)$params['begintm'];
		$endtm = (int)$params['endtm'];
		$attach = trim($params['attach']);		
		$result = Better_Poi_Notification::create(array(
			'poi_id' => $poi_id,			
			'creator' => Better_Registry::get('sess')->admin_uid,
			'title' => $content,
			'content' => $content,
			'image' => $attach,
			'begintm' => $begintm,
			'endtm' => $endtm,
			)) && $result=1;
		$this->sendAjaxResult($result);	
	}
	public function searchAction(){
		
		$params = $this->getRequest()->getParams();	
		$result=Better_Admin_Poi::getSpecial($params);	
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
		$this->view->categories = $poicategories;
	}
	
}

?>