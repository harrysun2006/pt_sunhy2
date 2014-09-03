<?php
/**
 * admin POI controller
 * @author yangl
 */
class Admin_UpdatepoiController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/updatepoi.js?ver='.BETTER_VER_CODE);
		$this->view->title="POI管理";		
	}
	
	public function indexAction()
	{ 
		$poiId = Better_Poi_Info::dehashId($this->getRequest()->getParam('id', ''));
		$poiDetail = array();
		
		if ($poiId>0) {
			$poiInfo = Better_Poi_Info::getInstance($poiId);
			$poiDetail = $poiInfo->get();
		}
		
		$this->view->headScript()->prependScript('
		var Better_Poi_Id =\''.$poiDetail['poi_id'].'\';
		var Better_Poi_Detail = '.json_encode($poiDetail).';
		');
		
		$poicategories=Better_Admin_Poi::getPOICategory();
		$this->view->categories = $poicategories;

		$this->view->poiDetail = $poiDetail;
		
	}
	
	
	public function updateAction(){
		$data=array();
		$result=0;
		$params = $this->getRequest()->getParams();
		$poi_id=isset($params['poi_id'])? $params['poi_id']:'';
		$category=isset($params['poi_cate'])? $params['poi_cate']:'';
		$name=isset($params['poi_name'])? $params['poi_name']:'';
		$city=isset($params['poi_city'])? $params['poi_city']:'';
		$address=isset($params['poi_address'])? $params['poi_address']:'';
		$tell=isset($params['poi_tell'])? $params['poi_tell']:'';
		//$major=isset($params['poi_major'])? $params['poi_major']:0;
		$lat=isset($params['poi_lat'])? $params['poi_lat']: 0;
		$lon=isset($params['poi_lon'])? $params['poi_lon']: 0;
		$label=isset($params['poi_label'])? $params['poi_label']: '';
		$intro=isset($params['poi_intro'])? $params['poi_intro']: '';
		$ownerid=isset($params['ownerid'])? $params['ownerid']: '';
		$certified = isset($params['certified'])? $params['certified']: 0;
		$closed = isset($params['closed']) ? $params['closed'] : 0;
		$forbid_major = isset($params['forbid_major']) ? $params['forbid_major'] : 0;
		$level_adjust = isset($params['level']) ? $params['level'] : '';
		
		list($x, $y) = Better_Functions::LL2XY($lon, $lat);
		
		$data=array(
			'category_id'=>$category,
			'name'=>$name,
			'city'=>$city,
			'address'=>$address,
			'phone'=>$tell,
			'x'=>$x,
			'y'=>$y,
			'label'=>$label,
			'intro'=>$intro,
			'ownerid'=>$ownerid,
			'certified'=>$certified,
			'closed'=>$closed,
			'forbid_major'=>$forbid_major,
			'level_adjust'=>$level_adjust
		);
		
		
		$poiInfo = Better_Poi_Info::getInstance($poi_id)->getBasic();
		if($poiInfo['poi_id'] && $ownerid && $poiInfo['ownerid']!=$ownerid){
			Better_Log::getInstance()->logInfo("店主：".$ownerid."POI:".$poi_id,'updatepoi');
			Better_Admin_Poi::updatePoiOwner($ownerid,$poi_id);			
		} else if($poiInfo['poi_id'] && $poiInfo['ownerid'] && !$ownerid) {
			Better_Admin_Poi::clearPoiOwner($poi_id);				
		}
		
		
		if($poi_id && count($data)>0){
			Better_Admin_Poi::updatePOI($data, $poi_id) && $result=1;
		}
		
		$this->sendAjaxResult($result);
		
	}
	
}

?>