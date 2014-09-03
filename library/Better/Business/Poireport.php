<?php

/**
 * POI促销
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Business_Poireport
{
	protected static $instance = array();
	
	protected function __construct($poiId)
	{
		parent::__construct($poiId);
	}
	
		
		
	public function getcheckingender($params)
	{
		$result = array();		
		$data = array(		
			'poi_id' => $params['poi_id'],
			'begtm' => $params['begtm'],
			'endtm' => $params['endtm'],
			'score' => $params['score']		
		);		
		$tempinfo = Better_DAO_Business_Poireport::getInstance()->getcheckingender($data);				
		return $tempinfo;
	}
	
	public function getcheckindays($params)
	{
		$result = array();		
		$data = array(		
			'poi_id' => $params['poi_id'],
			'begtm' => $params['begtm'],
			'endtm' => $params['endtm'],
			'score' => $params['score']			
		);		
		
		$tempinfo = Better_DAO_Business_Poireport::getInstance()->getcheckindays($data);
		$begtm = $params['begtm']+8*3600;
		$begdate = date("n-j",$begtm);
		
		$maxday = ceil(($params['endtm']-$params['begtm'])/(24*3600));	
		$days = array();
		$checkin = array();
		
		for($i=0;$i<$maxday;$i++){
			$thisday = date("n-j",$begtm+$i*3600*24);
			$days[] = date("n-j",$begtm+$i*3600*24);
			$checkin[] = 0;						
		}		
		foreach($tempinfo as $row){
			$keys = array_search($row['tmhour'],$days);			
			$checkin[$keys] = (int)$row['nums'];
		}	
	
		$day = array();
		foreach($days as $key=>$row){
			$day[$key]="&nbsp;";
			if($key%6==0){
				$day[$key]=$days[$key];
			}
		}
		$result = array(
			'days' => $day,
			'checkin' => $checkin
		);		
		return $result;
	}
	
	public function getcheckinhours($params)
	{
		$result = array();		
		$data = array(		
			'poi_id' => $params['poi_id'],
			'begtm' => $params['begtm'],
			'endtm' => $params['endtm'],
			'score' => $params['score']			
		);		
		$tempinfo = Better_DAO_Business_Poireport::getInstance()->getcheckinhours($data);				
		return $tempinfo;
	}
	
	public function getpoisync($params)
	{
		$result = array();		
		$data = array(		
			'poi_id' => $params['poi_id'],
			'begtm' => $params['begtm'],
			'endtm' => $params['endtm']			
		);		
		$tempinfo = Better_DAO_Business_Poireport::getInstance()->getpoisync($data);				
		return $tempinfo;
	}
}