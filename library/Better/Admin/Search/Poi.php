<?php
/**
 * 
 * 
 */
class Better_Admin_Search_Poi{

	private $method;
	private static $instance;
	private static $serverUrl = '';
	
	function __construct($method){
		$this->method = $method;
		self::$serverUrl = Better_Config::getAppConfig()->poi->fulltext->server;
	}
	
	
	public static function getInstance($method){
		if(!self::$instance){
			self::$instance = new self($method);
		}
		
		return self::$instance;
	}
	
	public function getPois(array $params){
		if($this->method==='mysql'){
			$results = Better_DAO_Admin_Poi::getInstance()->getAllPOIs($params);
			
		}else if($this->method==='fulltext'){
			//================================
			//	搜索参数初始化	START		//
			//================================
			$locFilter = true;
			
			$query = trim($params['keyword']);
			$range = $params['range'] ? (int)$params['range'] : 5000;
			
			$page = (int)$params['page'];
			$page<=0 && $page = 1;
			
			$count = (int)$params['count'];
			$count<=0 && $count = BETTER_PAGE_SIZE;
			
			$direction = trim($params['direction']);
			$direction || $direction = 'dir_all';
			
			$lon = trim($params['lon']);
			$lon || $lon = '';
			
			if (trim($params['lon_alpha'])) {
				$lonAlpha = trim($params['lon_alpha']);
			} else if ($range) {
				$lonAlpha = 1.2*($range/100000);
			} else {
				$lonAlpha = 0.01;
			}
			
			$lat = trim($params['lat']);
			$lat || $lat = '';
			if (trim($params['lat_alpha'])) {
				$latAlpha = trim($params['lat_alpha']);
			} else if ($range) {
				$latAlpha = 1.2*($range/100000);
			} else {
				$latAlpha = 0.01;
			}
			
			$start = ($page - 1)*$count;
				
			$poiparams = array(
				'wt' => 'json',
				'start' => $start,
				'rows' => $count,
				);
			if( $query == '' ){
				$poiparams['q'] = '*:* AND _val_:"recip(sum(product(level,0.00001),sum(pow(sub('.$lat.',lat),2),pow(sub('.$lon.',lon),2))),1,1,0)"';
			}else{
			 	$poiparams['q'] = $query.' AND _val_:"recip(sum(product(level,0.00001),sum(pow(sub('.$lat.',lat),2),pow(sub('.$lon.',lon),2))),1,1,0)"';
			}	
	
			if( $locFilter && $lat && $lon ){
				$latDown = $latUp = $lat;
				$lonDown = $lonUp = $lon;
				$latDown =  $lat - $latAlpha;
				$latUp  =  $lat + $latAlpha;
				$lonDown =  $lon - $lonAlpha;
				$lonUp  =  $lon + $lonAlpha;
				
				$poiparams['fq'] = '+lat:['.$latDown.' TO '.$latUp.']+lon:['.$lonDown.' TO '.$lonUp.']';			
			}
			
			//================================
			//	搜索参数初始化	END			//
			//================================
			$results = array('rows'=>array(), 'count'=>0);
			
			$client = new Zend_Http_Client(self::$serverUrl, array(
				'keepalive' => false
				));
			$client->setParameterGet($poiparams);
			$response = $client->request(Zend_Http_Client::GET);
			if ($response->getStatus() == 200) {
				$json = json_decode($response->getBody());
				$resultDocs = $json->{'response'};
				$results['count'] = $resultDocs->{'numFound'};
	
				$docs = $resultDocs->{'docs'};
				foreach ($docs as $doc) {
					$pid = (int)$doc->{'poi_id'}[0];
					$poi_info = Better_Poi_Info::getInstance($pid)->getBasic();
					if($poi_info['poi_id']){
						$rows[] = $poi_info;
					}
				}
				$results['rows'] = $rows;
			}
			
		}
	
		return $results;
	}
	
}