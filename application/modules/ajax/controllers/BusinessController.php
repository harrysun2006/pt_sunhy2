<?php
		
/**
 * POI相关统计相关
 * 
 * @package Controllers
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Ajax_BusinessController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init();	
	}	
	
	/**
	 * 搜索Poi
	 * 
	 * @return
	 */
	public function getcheckingenderAction(){		
		$begtm = (int)$this->getRequest()->getParam('begtm',0);
		$endtm = (int)$this->getRequest()->getParam('endtm',0);
		$poi_id = (int)$this->getRequest()->getParam('poi_id', 0);
		$params = array(
			'poi_id' => $poi_id,
			'begtm' => $begtm,
			'endtm' => $endtm
		);
		$result = Better_Business_Poireport::getcheckingender($params);
		$data = array();
		$totalnum = 0;
		foreach($result as $row){
			$totalnum +=$row['nums'];
		}		
		foreach($result as $key => $row){
			switch ($row['gender']){
				case 'male':
					$gender =$this->lang->global->poi->owner->male;
				break;
				case 'female':
					$gender =$this->lang->global->poi->owner->female;
				break;
				case 'secret':
					$gender =$this->lang->global->poi->owner->secret;
				break;
			}		
			$data[$key][]= $gender;	
			$data[$key][]= round($row['nums']/$totalnum,2)*100;					
		}				
		$this->output['result'] = json_encode(&$data);
				
		$this->output();
	}

	public function getcheckindaysAction(){		
		$begtm = (int)$this->getRequest()->getParam('begtm',0);
		$endtm = (int)$this->getRequest()->getParam('endtm',0);
		$poi_id = (int)$this->getRequest()->getParam('poi_id', 0);
		$params = array(
			'poi_id' => $poi_id,
			'begtm' => $begtm,
			'endtm' => $endtm
		);
		$result = Better_Business_Poireport::getcheckindays($params);			
		$this->output['days'] = json_encode(&$result['days']);	
		$this->output['checkin'] = json_encode(&$result['checkin']);			
		$this->output();
	}
	
	public function getpoisyncAction(){		
		$begtm = (int)$this->getRequest()->getParam('begtm',0);
		$endtm = (int)$this->getRequest()->getParam('endtm',0);
		$poi_id = (int)$this->getRequest()->getParam('poi_id', 0);
		$params = array(
			'poi_id' => $poi_id,
			'begtm' => $begtm,
			'endtm' => $endtm
		);
		$result = Better_Business_Poireport::getpoisync($params);
		$syncsite = Better_Service_PushToOtherSites::$shortProtocols;
		$supportedProtocols = Better_Service_PushToOtherSites::$openingProtocols;
		$date = array();		
		foreach($supportedProtocols as $row){
			$a = 0;					
			foreach($result as $trow){
				if($row==$trow['protocol']){
					$a = (int)$trow['nums'];										
				}
			}
			$endnums[] = $a;
		}	
		$this->output['sync_nums'] = json_encode(&$endnums);	
		$this->output['sync_site'] = json_encode(&$syncsite);			
		$this->output();
	}
	
	public function getcheckinhoursAction(){		
		$begtm = (int)$this->getRequest()->getParam('begtm',0);
		$endtm = (int)$this->getRequest()->getParam('endtm',0);
		$poi_id = (int)$this->getRequest()->getParam('poi_id', 0);
		$params = array(
			'poi_id' => $poi_id,
			'begtm' => $begtm,
			'endtm' => $endtm
		);
		$result = Better_Business_Poireport::getcheckinhours($params);		
		$totalcheckinhours = 0;
		$checkinhours = array(array('timehour'=>'04,05,06,07,08,09','times'=>'0','time_interval'=>$this->lang->global->poi->owner->morning),array('timehour'=>'10,11,12,13','times'=>'0','time_interval'=>$this->lang->global->poi->owner->noon),array('timehour'=>'14,15,16','times'=>'0','time_interval'=>$this->lang->global->poi->owner->afternoon),array('timehour'=>'17,18,19,20,21,22','times'=>'0','time_interval'=>$this->lang->global->poi->owner->night),array('timehour'=>'23,00,01,02,03','times'=>'0','time_interval'=>$this->lang->global->poi->owner->midnight));	
			foreach($result as $hours){
				$thehours = $hours['hours']+8;
				if($thehours>=24){
					$thehours = $thehours-24;
				}
				$thenums = $hours['nums'];
				$timehour = array();
				for($j=0;$j<count($checkinhours);$j++){
					$timehour = split(',',$checkinhours[$j]['timehour']);													
					if(in_array($thehours,$timehour)){
						$checkinhours[$j]['times'] = $checkinhours[$j]['times']+$thenums;						
					}
				}
				$totalcheckinhours = $totalcheckinhours+$thenums;					
			}	
		$endresult = array();			
		foreach($checkinhours as $row){
			if($totalcheckinhours>0){					
				$row['per'] = round($row['times']/$totalcheckinhours,2)*100;
			} else {
				$row['per'] = 0;
			}
			$endresult[] = $row;			
		}	
		$this->output['result'] = json_encode(&$endresult);	
		
		$this->output();
	}
	
	public function getpoispecialAction(){
		$poi_id = (int)$this->getRequest()->getParam('poi_id', 0);
		$specialdate = array(
				'poi_id' => $poi_id,
				'checked' => '0,1'
			);
		$data = array();
		$result = Better_Poi_Notification::getInstance($poi_id)->getPoispecial($specialdate);
		foreach($result['rows'] as $row){
			$data[] = $row;
		}
		$this->output['result'] = json_encode($data);
		$this->output();
	}
}