<?php
/**
 * 合并完成后修改poi
 */
class Admin_PoisupdateController extends Better_Controller_Admin{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/poisupdate.js?ver='.BETTER_VER_CODE);
		$this->view->title="修改POI";		
	}
	
	
	public function indexAction(){

		$params = $this->getRequest()->getParams();
		$result = Better_Admin_Poiupdate::getPOIs($params);

		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];		
	}
	
	
	public function updatexyAction(){
		$pois = $this->getRequest()->getParam('pids', array());
		$result = 0;
		
		if($pois && count($pois)>0){
			foreach($pois as $row){
				$poi_id = '';
				$lat = '';
				$lon = '';
				$id = '';
				
				$tmp = explode('@', $row);
				if($tmp[0] && $tmp[1] && $tmp[2]){
					$poi_id = $tmp[0];
					$_latlon = $tmp[1];
					$_tmp = explode('|', $_latlon);
					if($_tmp[0] && $_tmp[1]){
						$lat = $_tmp[0];
						$lon = $_tmp[1];
					}
					$id = $tmp[2];
				}
				
				if($poi_id && $lat && $lon && $id){
					list($x, $y) = Better_Functions::LL2XY($lon, $lat);
					$data = array(
						'x'=>$x,
						'y'=>$y
					);
					
					Better_Admin_Poi::updatePOI($data, $poi_id);	
					Better_DAO_Admin_Poiupdate::getInstance()->update(array('flag'=>1), $id);
				}
			}
			$result=1;
		}
		
		$this->sendAjaxResult($result);
	}
	
	
}