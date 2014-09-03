<?php

/**
 * POI相关api
 * 
 * @package 
 * @author 
 *
 */
class Public_PoiwithoutloginController extends Better_Controller_Public
{
	var $life_phonelink = array(
	            array('name'=>'苏州便民服务中心','phone'=>'12345'),
	          	array('name'=>'电话号码查询','phone'=>'114'),		
	            array('name'=>'自来水报修','phone'=>'67510862'),
	            array('name'=>'电力服务','phone'=>'95598'),
	            array('name'=>'煤气报修','phone'=>'68261319'),
	            array('name'=>'园区城市公用事业服务热线','phone'=>'6911100'),
	            array('name'=>'数字电视服务','phone'=>'96296'),               
	            array('name'=>'天气预报','phone'=>'121'),
	            array('name'=>'电话报时','phone'=>'12117'),
	            array('name'=>'鲜花预定','phone'=>'65811213'),
	            array('name'=>'物价举报','phone'=>'12358'),		
	            array('name'=>'工商消费投诉','phone'=>'12315'),
	            array('name'=>'市消协','phone'=>'68150315'),
	            array('name'=>'新区消协','phone'=>'68094315'),
	            array('name'=>'园区消协','phone'=>'66600315'),
	            array('name'=>'质检热线','phone'=>'12365'),	
	            array('name'=>'环境举报','phone'=>'12369'),
	            array('name'=>'法律援助热线','phone'=>'12348'),
	            array('name'=>'反贪举报','phone'=>'85182000'),          
			    array('name'=>'市卫生监督所投诉','phone'=>'68653884'),
	            array('name'=>'洞庭山送水','phone'=>'96000'),
	            array('name'=>'顺丰快递','phone'=>'4008111111'),
	            array('name'=>'邮政EMS','phone'=>'67218335'),
	            array('name'=>'光明牛奶','phone'=>'65301717'),
	            array('name'=>'双喜牛奶','phone'=>'66625777'),
	            array('name'=>'姑苏晚报','phone'=>'65115275'),
	            array('name'=>'苏州日报','phone'=>'62513765')
			    );
		var $waimai_phonelink = array(
				array('name'=>'肯德基','phone'=>'4008-823-823'),			
				array('name'=>'必胜客','phone'=>'4008-123-123'),
				array('name'=>'棒!约翰','phone'=>'4008-887-272')
				);
	
		var $shopping_phonelink = array(
				array('name'=>'美罗商城','phone'=>'65223777'),
				array('name'=>'泰华商城','phone'=>'65302158'),
				array('name'=>'石路国际','phone'=>'65322511'),
				array('name'=>'天虹百货','phone'=>'62990188'),
				array('name'=>'久光百货','phone'=>'67333600'),
				array('name'=>'人民商场','phone'=>'65221252'),
				array('name'=>'大洋百货','phone'=>'65246888'),
				array('name'=>'金鹰国际','phone'=>'67709888'),
				array('name'=>'长发商厦','phone'=>'67278107'),
				array('name'=>'万千百货','phone'=>'4008158866'),
				); 
	
		var $hospital_phonelink = array(
				array('name'=>'一院','phone'=>'65223637'),			
				array('name'=>'五院','phone'=>'65203696'),
				array('name'=>'市立','phone'=>'69009090'),
				array('name'=>'口腔','phone'=>'67275765'),
	            array('name'=>'中医','phone'=>'65222220'),
				array('name'=>'儿童','phone'=>'65223820'),
				array('name'=>'园区疾控','phone'=>'67611603'),
				array('name'=>'圣爱','phone'=>'68080101'),
				array('name'=>'九龙','phone'=>'62629999'),
				array('name'=>'广济','phone'=>'65334383'),
				array('name'=>'东吴中西医','phone'=>'68120120'),
				array('name'=>'苏州大学附属第一医院','phone'=>'65223637'),
				array('name'=>'苏州大学附属第二医院','phone'=>'68282030'),
				array('name'=>'苏州市眼视光医院 ','phone'=>'65163588'),
				array('name'=>'苏州同济医院','phone'=> '68111999'),
				array('name'=>'苏州市吴中人民医院','phone'=>'65284205'),
				array('name'=>'张家港市第一人民医院','phone'=>'56919999'),
				array('name'=>'常熟市第一人民医院','phone'=>'52772228'),
				array('name'=>'昆山市第一人民医院 ','phone'=>'57530744'),
				array('name'=>'太仓市第一人民医院','phone'=> '53101356'),
				array('name'=>'吴江市第一人民医院','phone'=>'63000519')			
				);
	
			var $bank_phonelink = array(
				array('name'=>'中行','phone'=>'95566'),			
	            array('name'=>'建行','phone'=>'95533'),			
	            array('name'=>'工行','phone'=>'95588'),
				array('name'=>'农行','phone'=>'95599'),
				array('name'=>'交行','phone'=>'95559'),
				array('name'=>'招行','phone'=>'95555'),
	            array('name'=>'中信','phone'=>'95558'),
				array('name'=>'兴业','phone'=>'95561'),
				array('name'=>'民生','phone'=>'95568'),
				array('name'=>'光大','phone'=>'95595'),
				array('name'=>'华夏','phone'=>'95577'),
		        array('name'=>'江苏银行','phone'=>'4008696098'),
	            array('name'=>'深发展','phone'=>'95501'),    
				array('name'=>'浦发展','phone'=>'95528'),
	            array('name'=>'广发展','phone'=>'95508'),
	            array('name'=>'苏州银行','phone'=>'96067'),            
	            array('name'=>'渣打','phone'=>'67630198'),    
				array('name'=>'汇丰','phone'=>'400-882-6688'),
	            array('name'=>'邮蓄','phone'=>'95580'),
	            array('name'=>'人保财险','phone'=>'95518'),
	            array('name'=>'太保财险','phone'=>'95500'),    
				array('name'=>'平安财险','phone'=>'955128'),
	            array('name'=>'大地保险','phone'=>'95590'),
	            array('name'=>'太保寿险','phone'=>'95500'),
	            array('name'=>'太平人寿','phone'=>'95589'),    
				array('name'=>'平安人寿','phone'=>'95511'),
	            array('name'=>'信诚人寿','phone'=>'4008838838')
				);
	
			var $tel_phonelink = array(
				array('name'=>'联通','phone'=>'10010'),
				array('name'=>'电信','phone'=>'10000'),			
				array('name'=>'移动','phone'=>'10086'),
				array('name'=>'邮政','phone'=>'11185'),
				array('name'=>'天音科技', 'url'=>'','address'=>'苏州市景德路122号二/三楼','phone'=>'65158166'),
				array('name'=>'天音昆山', 'url'=>'','address'=>'昆山市朝阳西路236-1号','phone'=>'57397789'),
				array('name'=>'天音常熟', 'url'=>'','address'=>'江苏省常熟市海虞南路67号','phone'=>'52222150'),
				array('name'=>'天音张家港', 'url'=>'','address'=>'江苏省张家港市沙洲中路78号','phone'=>'58790150')
				);
	
			var $gov_phonelink = array(			
				array('name'=>'经信委','phone'=>'68615614'),			
				array('name'=>'教育局','phone'=>'65151226'),
				array('name'=>'档案局','phone'=>'68617327'),
				array('name'=>'公安局','phone'=>'65225661'),
				array('name'=>'司法局','phone'=>'65231005'),
				array('name'=>'工商局','phone'=>'12315'),
				array('name'=>'人事局','phone'=>'65212164'),
				array('name'=>'国土局','phone'=>'65293875'),
				array('name'=>'交通局','phone'=>'65222448'),			
				array('name'=>'旅游局','phone'=>'65213140'),			
				array('name'=>'信访局','phone'=>'65563309'),
				array('name'=>'物价局','phone'=>'68267827'),
				array('name'=>'质监局','phone'=>'65225050'),
				array('name'=>'药监局','phone'=>'65306637'),
				array('name'=>'民政局','phone'=>'65226408'),
				array('name'=>'地税局','phone'=>'68651828'),
				array('name'=>'房管局','phone'=>'65110636'),			
				array('name'=>'城管局','phone'=>'65180683'),			
				array('name'=>'邮政局','phone'=>'68635566'),						
				array('name'=>'价格认证中心','phone'=>'68362912'),
				array('name'=>'劳动和社会保障局','phone'=>'12333'),
				array('name'=>'劳动就业管理中心','phone'=>'68295466')						
				);	
				
				
	       var $trip_phonelink = array(
	         	array('name'=>'航班查询','phone'=>'65104881'),
	         	array('name'=>'国航客服热线','phone'=>'4008-100-999'),
	         	array('name'=>'东航客服热线','phone'=>'95530'),
	         	array('name'=>'南航客服热线','phone'=>'95539'),
	         	array('name'=>'东航昆山售票','phone'=>'57552500'),
	         	array('name'=>'苏州火车站问询','phone'=>'95105105'),
	         	array('name'=>'观前票务中心','phone'=>'67706676'),
	         	array('name'=>'长途客运咨询','phone'=>'65776577'),
	         	array('name'=>'汽车北站咨询','phone'=>'67530686'),
	        	array('name'=>'汽车南站咨询','phone'=>'87181061'),
	         	array('name'=>'汽车西站咨询','phone'=>'68255168'),
	         	array('name'=>'常熟汽车站','phone'=>'52752836'),
	         	array('name'=>'昆山汽车站','phone'=>'57302812'),
	         	array('name'=>'张家港汽车站','phone'=>'58688000'),
	         	array('name'=>'吴江汽车站','phone'=>'63425023'),
	         	array('name'=>'太仓汽车站','phone'=>'53522999'),
	        	array('name'=>'出租车电话','phone'=>'67776777'),
	         	array('name'=>'出租投诉','phone'=>'96196'),
	         	array('name'=>'失物查找(出租车)','phone'=>'12345'),
	         	array('name'=>'公交车咨询','phone'=>'65252755'),
	         	array('name'=>'轮船客运处','phone'=>'65208484'),
	         	array('name'=>'轮船运输公司','phone'=>'65206681'),
	         	array('name'=>'联运人民路售票处','phone'=>'66335840'),
	         	array('name'=>'常熟联运公司售票中心','phone'=>'52233591'),
	         	array('name'=>'联运昆山售票处','phone'=>'57554718'),
	        	array('name'=>'旅游咨询','phone'=>'65203131'),
	         	array('name'=>'旅游投诉','phone'=>'65223377'),
	            array('name'=>'交通服务热线','phone'=>'96520'), 
	        	array('name'=>'交通违章查询','phone'=>'65151271'),
	        	array('name'=>'客运管理处', 'phone'=>'68668578'),
				array('name'=>'巴士公交监督', 'phone'=>'67538484')
	            );
	            
	      var $police_phonelink = array(
				array('name'=>'公安局110指挥中心','phone'=>'65156491'), 
				array('name'=>'治安警察支队','phone'=>'65685447'), 
				array('name'=>'经济犯罪侦察支队','phone'=>'65188176'),
				array('name'=>'刑事侦察支队','phone'=>'65222270'),
				array('name'=>'公安局水警支队（水上分局）','phone'=>'68251952'),
				array('name'=>'市公安平江分局','phone'=>'67510238'),
				array('name'=>'市公安沧浪分局','phone'=>'68214535'),
				array('name'=>'市公安局金阊分局','phone'=>'65329930'),
				array('name'=>'市公安局工业园区分局','phone'=>'66601511'),
				array('name'=>'市公安吴中分局','phone'=>'65259280'), 
				array('name'=>'市公安相城分局','phone'=>'65761960'), 
				array('name'=>'市公安虎丘分局','phone'=>'68212328'),
				array('name'=>'太湖国家旅游度假区分局','phone'=>'66510110'),
				array('name'=>'市公安局公交分局','phone'=>'65139708'), 
				array('name'=>'火车站派出所','phone'=>'67534829'), 
				array('name'=>'汽车南站派出所','phone'=>'65778292'), 
				array('name'=>'交巡警支队','phone'=>'65151271'),
				array('name'=>'交巡警机动大队','phone'=>'65151255'),
				array('name'=>'交巡警事故大队','phone'=>'68620678'),
				array('name'=>'交巡警金阊大队','phone'=>'65512259'), 
				array('name'=>'交巡警平江大队','phone'=>'67533472'), 
				array('name'=>'交巡警沧浪大队','phone'=>'68227099'),  
				array('name'=>'交巡警虎丘大队','phone'=>'66079686'),  
				array('name'=>'交巡警园区大队','phone'=>'67244600'),  
				array('name'=>'交巡警吴中大队','phone'=>'65251132'), 
				array('name'=>'交巡警相城大队','phone'=>'65756710'), 
				array('name'=>'交巡警太湖度假区大队','phone'=>'66211684'),  
				array('name'=>'交巡警沪宁高速大队','phone'=>'67522100'),
				array('name'=>'交巡警苏嘉杭高速大队','phone'=>'65975177'),			
				array('name'=>'车辆管理所吴中分所','phone'=>'65252867'),
				array('name'=>'车辆管理所非机动科','phone'=>'65232559'),
				array('name'=>'观山考验场','phone'=>'66716886'),        
				array('name'=>'交通管理咨询','phone'=>'96122')
	 			);
	 	//error_log(serialize($this->life_phonelink),3,'d://phone.log');
	 	var $phonelist = array(
			array('category'=>'生活服务','shownum'=>'1','phoneblock'=>'life_phonelink'),
			array('category'=>'外卖','shownum'=>'4','phoneblock'=>'waimai_phonelink'),
			array('category'=>'购物','shownum'=>'3','phoneblock'=>'shopping_phonelink'),
			array('category'=>'出行','shownum'=>'2','phoneblock'=>'trip_phonelink'),
			array('category'=>'医院','shownum'=>'6','phoneblock'=>'hospital_phonelink'),
			array('category'=>'银行/保险','shownum'=>'4','phoneblock'=>'bank_phonelink'),
			array('category'=>'通信','shownum'=>'5','phoneblock'=>'tel_phonelink'),	
	        array('category'=>'警务','shownum'=>'2','phoneblock'=>'police_phonelink'),
			array('category'=>'机关事业单位','shownum'=>'2','phoneblock'=>'gov_phonelink')
		);
	public function init()
	{
		parent::init();
		$this->xmlRoot = 'pois';		
	}

	/**
	 * 附近贴士
	 * 
	 * @return
	 */
	
	
	public function publictimelineAction()
	{
		$this->needLbsLog = true;
		$this->xmlRoot = 'poispace_public';
		if(!$this->uid){
			$this->user = Better_User::getInstance(10000);
		}		
		list($lon, $lat, $range) = $this->mixLL();
		$range = $this->getRequest()->getParam('range', 5000);
		$query = trim(urldecode($this->getRequest()->getParam('query', '')));
		$poi = (bool)($this->getRequest()->getParam('poi', 'false')=='true' ? true : false);
		$tip = (bool)($this->getRequest()->getParam('tip', 'false')=='true' ? true : false);
		$coupon = (bool)($this->getRequest()->getParam('coupon', 'false')=='true' ? true : false);
		$checkined = (bool)($this->getRequest()->getParam('checkined', 'false')=='true' ? true : false);
		$favTips = (bool)($this->getRequest()->getParam('favorited_tips', 'false')=='true' ? true : false);
		$friendsTips = (bool)($this->getRequest()->getParam('friends_tips', 'false')=='true' ? true : false);
		
		$data = array(
			'place' => array(),
			'pois' => array(),
			'tips' => array(),
			'coupons' => array(),
			'favorited_tips' => array(),
			'checkined_pois' => array(),
			'friends_tips' => array(),
			);
		
		$poiParams = array(
			'what' => 'poi',
			'page' => $this->page,
			'count' => $this->count,
			);
		$tipsParams = array(
			'what' => 'blog',
			'type' => 'tips',
			'page' => $this->page,
			'count' => $this->count,
			);
		$couponsParams = array(
			'page' => $this->page,
			'count' => $this->count,
			);
		
		if ($lon && $lat) {
			$geo = new Better_Service_Geoname();
			$geoInfo = $geo->getGeoName($lon, $lat);
			
			$address = '';
			if (trim($geoInfo['r1']) && trim($geoInfo['r2'])) {
				$address = str_replace('{NO1}', $geoInfo['r1'], $this->lang->poi->place->string);
				$address = str_replace('{NO2}', $geoInfo['r2'], $address);
			}

			$data['place'] = $this->api->getTranslator('place')->translate(array(
				'lon' => $lon,
				'lat' => $lat,
				'address' => $address,
				'city' => $geoInfo['name'],
				));			
				
			$poiParams['lon'] = $lon;
			$poiParams['lat'] = $lat;
			$poiParams['range'] = $range;
			
			$tipsParams['lon'] = $lon;
			$tipsParams['lat'] = $lat;
			$tipsParams['range'] = 5000;
			
			$couponsParams['lon'] = $lon;
			$couponsParams['lat'] = $lat;
			$couponsParams['range'] = 99999999;
		} 

		//pois
		if ($poi==true) {
			if ($this->ft()) {
				$poiParams['method'] = 'fulltext';
			}
			
			$rows = Better_Search::factory($poiParams)->search();
			foreach ($rows['rows'] as $row) {
				$data['pois'][] = array(
					'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		}		
		
			//	tips
		if ($tip==true) {
			//$rows = Better_Search::factory($tipsParams)->search();
			
			$rows = Better_Poi_Tips::getRangedTips($tipsParams);
			Better_Log::getInstance()->logInfo(serialize($rows),'testg3');
			foreach ($rows['rows'] as $row) {
				$data['tips'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}						
		}			
		
		// coupons
		if ($coupon==true) {
			$rows = Better_Poi_Notification::search($couponsParams);
			foreach ($rows['rows'] as $row) {
				$data['coupons'][] = array(
					'coupon' => $this->api->getTranslator('coupon')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		}					
		
		//	Checkined Pois
		if ($checkined==true) {
			//$rows = $this->user->checkin()->checkinedPois($this->page, $this->count);
			$rows = $this->user->checkin()->fuckingCheckinedPoisByDistance(array(
				'page' => $this->page,
				'count' => $this->count,
				'lon' => $lon,
				'lat' => $lat,
				'range' => $range
				));

			foreach ($rows['rows'] as $row) {
				$data['checkined_pois'][] = array(
					'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}			
		}
		
		//	Favorited Tips
		if ($favTips) {
			$rows = $this->user->favorites()->allTips($this->page, $this->count);
			foreach ($rows['rows'] as $row) {
				$data['favorited_tips'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						))
					);
			}
		}
		
		//	Friends Tips
		if ($friendsTips) {
			$rows = $this->user->blog()->friendsTips($this->page, $this->count);
			foreach ($rows['rows'] as $row) {
				$data['friends_tips'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						))
					);
			}			
		}
		
		$this->data[$this->xmlRoot] = &$data;
		
		$this->output();
	}
	public function lltocityAction()
	{
		$this->needLbsLog = true;
		$this->xmlRoot = 'll_city';
		
		$lon = $this->getRequest()->getParam('lon', '');
		$lat = $this->getRequest()->getParam('lat', '');

		$geo = new Better_Service_Geoname();
		$geoInfo = $geo->getGeoName($lon, $lat);
		if(strlen($geoInfo['name'])<1){
			$this->error('error.poi.notfoundcity');
		} else {
			$address = '';
			if (trim($geoInfo['r1']) && trim($geoInfo['r2'])) {
					$address = str_replace('{NO1}', $geoInfo['r1'], $this->lang->poi->place->string);
					$address = str_replace('{NO2}', $geoInfo['r2'], $address);
			}
			
			$data['place'] = $this->api->getTranslator('place')->translate(array(
				'lon' => $lon,
				'lat' => $lat,
				'address' => $address,
				'city' => $geoInfo['name'],
				));		
			$this->data[$this->xmlRoot] = &$data;
		}
		$this->output();
	}
	
	
	
	/**
	 * 7.8 获取地点分类列表
	 * 
	 * @return
	 */
	public function categoriesAction()
	{
		$this->xmlRoot = 'categories';
		
		$langKey = $this->user->getUserLanguage();
		$cs = Better_Poi_Category::getAvailableCategories();
		
		foreach ($cs as $row) {
			$row['category_name'] = Better_Language::loadDbKey('category_name', $row, $langKey);
			$this->data[$this->xmlRoot][] = array(
				'category' => $this->api->getTranslator('poi_class')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)
				));
		}

		$this->output();
	}
	
	public function showAction()
	{
		//$userInfo = $this->auth();
		//if(!$this->uid){
			$this->user = Better_User::getInstance(10000);
			$userInfo = $this->user->getUserInfo();
		//}	
		$abId = trim($this->getRequest()->getParam('abid', ''));
		$id = Better_Poi_Info::dehashId($this->getRequest()->getParam('id', $abId));
		$visitors = (bool)($this->getRequest()->getParam('visitors', 'false')=='true' ? true : false);
		$shout = (bool)($this->getRequest()->getParam('shout', 'false')=='true' ? true : false);
		$tip = (bool)($this->getRequest()->getParam('tip', 'false')=='true' ? true : false);
		$checkin = (bool)($this->getRequest()->getParam('checkin', 'false')=='true' ? true : false);
		$treasure = (bool)($this->getRequest()->getParam('treasure', 'true')=='false' ? false : true);
		$poiFlag = (bool)($this->getRequest()->getParam('poi', 'true')=='false' ? false : true);	
		
		$this->xmlRoot = 'poispace';
		$data = array(
			'poi'	=> array(),
			'visitors' => array(),
			'statuses_normal' => array(),
			'statuses_tip' => array(),
			'statuses_checkin' => array(),
			'treasures' => array(),
			);
			
		if (BETTER_AIBANG_POI && $id && !is_numeric($id)) {
			$id = Better_Service_Aibang_Pool::ab2our($id);
		}

		$poi = Better_Poi_Info::getInstance($id);
		if ($poi->poi_id) {
			//	poi
			$poiDetail = $poi->get();
			
			if ($poiDetail['closed']==0) {
				if ($poiFlag==true) {
					$data['poi'] = $this->api->getTranslator('poi')->translate(array(
						'data' => &$poiDetail,
						'userInfo' => &$userInfo
						));
				}
					
				//	treasures
				if ($treasure==true) {
					$rows = Better_Poi_Treasure::getInstance($poi->poi_id)->logs($this->page, $this->count);
					if ($rows['count']>0) {
						foreach ($rows['rows'] as $row) {
							if (isset($row['treasure_detail']['id'])) {
								$data['treasures'][] = array(
									'treasure' => $this->api->getTranslator('treasure')->translate(array(
										'data' => &$row['treasure_detail'],
										'userInfo' => &$this->userInfo
										)),
									);
							}
						}
					}
				}
	
				//	visitors
				if ($visitors==true) {
					$rows = Better_Poi_Checkin::getInstance($poi->poi_id)->users($this->page, $this->count);
					if ($rows['total']>0) {
						foreach ($rows['rows'] as $row) {
							$data['visitors'][] = array(
								'visitor' => $this->api->getTranslator('user_concise')->translate(array(
									'data' => &$row,
									'userInfo' => &$userInfo,
									)),
								);
						}
					}
				}
	
				// shout
				if ($shout==true) {
					$rows = $this->user->blog()->getAllBlogs(array(
						'type' => array('normal'),
						'poi' => (array)$poi->poi_id,
						'page' => $this->page,
						), $this->count);
					foreach ($rows['rows'] as $row) {
						$data['statuses_normal'][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => $userInfo,
								)),
							);
					}
				}
				
				// tip
				if ($tip==true) {
					$rows = Better_Poi_Tips::getRangedTips(array(
						'poi_id' => $poi->poi_id,
						'page' => $this->page,
						'count' => $this->count,
						'order' => 'poll'
						));
		
					foreach ($rows['rows'] as $row) {
						$data['statuses_tip'][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => $userInfo,
								)),
							);
					}				
				}
				
				// checkin
				if ($checkin==true) {
					$rows = $this->user->blog()->getAllBlogs(array(
						'type' => 'checkin',
						'poi' => $poi->poi_id,
						'page' => $this->page,
						'count' => $this->count
						));
						
					foreach ($rows['rows'] as $row) {
						$data['statuses_checkin'][] = array(
							'status' => $this->api->getTranslator('status')->translate(array(
								'data' => &$row,
								'userInfo' => &$userInfo,
								)),
							);
					}
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.poi.was_closed');				
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.poi.invalid_poi');
		}
		
		$this->data[$this->xmlRoot] = &$data;
		
		$this->output();
	}
	/**
	 * 7.7 新增POI
	 * 
	 * @return
	 */
	public function searchAction()
	{
		
		$lon = $this->getRequest()->getParam('lon', '');
		$lat = $this->getRequest()->getParam('lat', '');
		$range = $this->getRequest()->getParam('range', 50000);
		$query = trim(urldecode($this->getRequest()->getParam('query', '')));
		$category = (int)$this->getRequest()->getParam('category', 0);
		$certified = (bool)($this->getRequest()->getParam('certified', 'false')=='false' ? false : true);
		$ver = $this->getRequest()->getParam('ver', 1);

		switch ($ver) {
			case '2':
				$this->xmlRoot = 'poi_search';
				
				$this->data[$this->xmlRoot]['place'] = array();
				$this->data[$this->xmlRoot]['pois'] = array();
				
				$poiParams = array(
					'what' => 'poi',
					'lon' => $lon,
					'lat' => $lat,
					'range' => $range,
					'certified' => $certified,
					'keyword' => $query,
					'order' => 'distance',
					'category' => $category,
					'page' => $this->page,
					'count' => $this->count,
					'level' => $accuracy
					);				
					
				if ($lon && $lat) {
					$geo = new Better_Service_Geoname();
					$geoInfo = $geo->getGeoName($lon, $lat);
					
					$address = '';
					if (trim($geoInfo['r1']) && trim($geoInfo['r2'])) {
						$address = str_replace('{NO1}', $geoInfo['r1'], $this->lang->poi->place->string);
						$address = str_replace('{NO2}', $geoInfo['r2'], $address);
					}
		
					$this->data[$this->xmlRoot]['place'] = $this->api->getTranslator('place')->translate(array(
						'lon' => $lon,
						'lat' => $lat,
						'address' => $address,
						'city' => $geoInfo['name'],
						));			
						
					$poiParams['lon'] = $lon;
					$poiParams['lat'] = $lat;
					$poiParams['range'] = $range;
				} 				

				if ($this->ft()) {
					$poiParams['method'] = 'fulltext';
				}
				$rows = Better_Search::factory($poiParams)->search();
				if (count($rows['rows'])==0) {
					$newPoiParams = $poiParams;
					$newPoiParams['keyword'] = 'more:('.$query.')';
					$rows = Better_Search::factory($newPoiParams)->search();
				}
				
				foreach ($rows['rows'] as $row) {
					$this->data[$this->xmlRoot]['pois'][] = array(
						'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
							)),
						);
				}				
				break;
			default:
				$this->xmlRoot = 'pois';
		
				$rows = Better_Search::factory(array(
					'what' => 'poi',
					'lon' => $lon,
					'lat' => $lat,
					'range' => $range,
					'certified' => $certified,
					'keyword' => $query,
					'order' => 'distance',
					'category' => $category,
					'page' => $this->page,
					'count' => $this->count,
					'method' => $this->ft() ? 'fulltext' : 'mysql'
					))->search();
			
				foreach ($rows['rows'] as $row) {
					$this->data[$this->xmlRoot][] = array(
						'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
							)),
						);
				}
				break;
		}
		
		//	记录poi搜索日志
		if ($this->page==1 && count($rows['rows'])>0) {
			Better_Poi_Search_Log::getInstance()->log(array(
				'uid' => $this->uid,
				'lon' => $lon,
				'lat' => $lat,
				'keyword' => $query,
				'results' => (int)$rows['count'],
				'range' => $range
				));
		} else if ($this->page==1 && count($rows['rows'])==0 && APPLICATION_ENV=='production') {
			Better_Poi_Search_Log::getInstance()->logEmpty(array(
				'uid' => $this->uid,
				'lon' => $lon,
				'lat' => $lat,
				'keyword' => $query,
				'results' => (int)$rows['count'],
				'range' => $range
				));			
		}
		
		$this->output();
		

	}
	public function iphonepublictimelineAction()
	{
		$this->needLbsLog = true;
		$this->xmlRoot = 'poispace_public';
		if(!$this->uid){
			$this->user = Better_User::getInstance(10000);
		}		
		list($lon, $lat, $range) = $this->mixLL();
		$range = $this->getRequest()->getParam('range', 5000);
		$query = trim(urldecode($this->getRequest()->getParam('query', '')));
		$poi = (bool)($this->getRequest()->getParam('poi', 'false')=='true' ? true : false);
		$tip = (bool)($this->getRequest()->getParam('tip', 'false')=='true' ? true : false);
		$coupon = (bool)($this->getRequest()->getParam('coupon', 'false')=='true' ? true : false);
		$checkined = (bool)($this->getRequest()->getParam('checkined', 'false')=='true' ? true : false);
		$favTips = (bool)($this->getRequest()->getParam('favorited_tips', 'false')=='true' ? true : false);
		$friendsTips = (bool)($this->getRequest()->getParam('friends_tips', 'false')=='true' ? true : false);
		$jump_switch = (bool)($this->getRequest()->getParam('jump_switch', 'false')=='true' ? true : false);
		$defaultll = 0;
		$t = 0;
		for ($i=1; $i<2; $i++) {	
			if($defaultll){
				$lat=31.320548;
				$lon=120.63869;
				$data = array(
					'place' => array(),
					'pois' => array(),
					'tips' => array(),
					'coupons' => array(),
					'favorited_tips' => array(),
					'checkined_pois' => array(),
					'friends_tips' => array(),
					'doublestr' => $doublestr
					);
			} else {
				$data = array(
					'place' => array(),
					'pois' => array(),
					'tips' => array(),
					'coupons' => array(),
					'favorited_tips' => array(),
					'checkined_pois' => array(),
					'friends_tips' => array(),					
					);
			}
			
			$poiParams = array(
				'what' => 'poi',
				'page' => $this->page,
				'count' => $this->count,
				);
			$tipsParams = array(
				'what' => 'blog',
				'type' => 'tips',
				'page' => $this->page,
				'count' => $this->count,
				);
			$couponsParams = array(
				'page' => $this->page,
				'count' => $this->count,
				);
			
			if ($lon && $lat) {
				$geo = new Better_Service_Geoname();
				$geoInfo = $geo->getGeoName($lon, $lat);
				
				$address = '';
				if (trim($geoInfo['r1']) && trim($geoInfo['r2'])) {
					$address = str_replace('{NO1}', $geoInfo['r1'], $this->lang->poi->place->string);
					$address = str_replace('{NO2}', $geoInfo['r2'], $address);
				}
	
				$data['place'] = $this->api->getTranslator('place')->translate(array(
					'lon' => $lon,
					'lat' => $lat,
					'address' => $address,
					'city' => $geoInfo['name'],
					));			
					
				$poiParams['lon'] = $lon;
				$poiParams['lat'] = $lat;
				$poiParams['range'] = $range;
				
				$tipsParams['lon'] = $lon;
				$tipsParams['lat'] = $lat;
				$tipsParams['range'] = 5000;
				
				$couponsParams['lon'] = $lon;
				$couponsParams['lat'] = $lat;
				$couponsParams['range'] = 99999999;
			} 
	
			//pois
			if ($poi==true) {
				if ($this->ft()) {
					$poiParams['method'] = 'fulltext';
				}
				
				$rows = Better_Search::factory($poiParams)->search();
				if(count($rows['rows'])<1){
						$t++;
							if ($t == 2)
								break;							
							$i = 0; 
							$defaultll = 1;
							$doublestr = '您附近没有地点，推荐苏州以下地点供参考';
							continue;
				}
				foreach ($rows['rows'] as $row) {
					$data['pois'][] = array(
						'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
							'doublerequest' => $defaultll,
							)),
						);
				}				
			}		
			
				//	tips
			if ($tip==true) {
				//$rows = Better_Search::factory($tipsParams)->search();
				
				$rows = Better_Poi_Tips::getRangedTips($tipsParams);
				Better_Log::getInstance()->logInfo(serialize($rows),'testg3');
				if(count($rows['rows'])<1){
					$t++;
						if ($t == 2)
							break;						
						$i = 0; 
						$defaultll = 1;
						$doublestr = '您附近没有贴士，推荐苏州以下贴士供参考';
						continue;
				}
				foreach ($rows['rows'] as $row) {
					$data['tips'][] = array(
						'status' => $this->api->getTranslator('status')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,							
							)),						
						);
				}							
			}			
			
			// coupons
			if ($coupon==true) {
				$rows = Better_Poi_Notification::search($couponsParams);
				foreach ($rows['rows'] as $row) {
					$data['coupons'][] = array(
						'coupon' => $this->api->getTranslator('coupon')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
							)),
						);
				}
			}					
			
			//	Checkined Pois
			if ($checkined==true) {
				//$rows = $this->user->checkin()->checkinedPois($this->page, $this->count);
				$rows = $this->user->checkin()->fuckingCheckinedPoisByDistance(array(
					'page' => $this->page,
					'count' => $this->count,
					'lon' => $lon,
					'lat' => $lat,
					'range' => $range
					));
	
				foreach ($rows['rows'] as $row) {
					$data['checkined_pois'][] = array(
						'poi_concise' => $this->api->getTranslator('poi_concise')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo,
							)),
						);
				}			
			}
			
			//	Favorited Tips
			if ($favTips) {
				$rows = $this->user->favorites()->allTips($this->page, $this->count);
				foreach ($rows['rows'] as $row) {
					$data['favorited_tips'][] = array(
						'status' => $this->api->getTranslator('status')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo
							))
						);
				}
			}
			
			//	Friends Tips
			if ($friendsTips) {
				$rows = $this->user->blog()->friendsTips($this->page, $this->count);
				foreach ($rows['rows'] as $row) {
					$data['friends_tips'][] = array(
						'status' => $this->api->getTranslator('status')->translate(array(
							'data' => &$row,
							'userInfo' => &$this->userInfo
							))
						);
				}			
			}
		}
		$jump_switch = 0;
		if($jump_switch && $tip){
			$this->data[$this->xmlRoot] = array('jumpurl'=>'http://sz3g.k.ai/3g');
		}  else {
			$this->data[$this->xmlRoot] = &$data;
		}
		$this->output();
	}
	public function iphonephonelistAction()
	{
		$this->needLbsLog = true;
		$this->xmlRoot = 'phonelist';	
			
		$jump_switch = 0;
		$category_id = (int)($this->getRequest()->getParam('category_id', '')!=''? $this->getRequest()->getParam('category_id', '') : -1);
		$range = (int)$this->getRequest()->getParam('type', '');
		if($jump_switch){
			$this->data[$this->xmlRoot] = array('jumpurl'=>'http://k.ai');
		}  else if($category_id>-1){
			$tempphonecategory = $this->phonelist[$category_id]['phoneblock'];
			$tempphonelist = $this->{$tempphonecategory};	
			$phoneblock = array();
			$category = array();
			foreach($tempphonelist as $row){				
				$phoneblock[]['phone'] = array(					
					"name" => $row['name'],
					"no" => $row['phone']
				);
			}			
			$category = array(
				"id" => $category_id,
				"name" => $this->phonelist[$category_id]['category'],
				"phoneblock" => $phoneblock
			);			
			$this->data[$this->xmlRoot]['category'] = &$category;
		} 
		$this->output();
	}
	public function iphonephonecategoryAction()
	{
		$this->needLbsLog = true;
		$this->xmlRoot = 'phonelist';		
		
		$jump_switch = 0;
		$category_id = (int)($this->getRequest()->getParam('category_id', '')!=''? $this->getRequest()->getParam('category_id', '') : -1);
		$range = (int)$this->getRequest()->getParam('type', '');
		if($jump_switch){
			$this->data[$this->xmlRoot] = array('jumpurl'=>'http://k.ai');
		}  else {		
			$category = array();					
			foreach($this->phonelist as $key =>$rows){	
				$phoneblockname = $rows['phoneblock'];				
				$tempphoneblock = $this->{$phoneblockname};	
						
				$tempphone = array_slice($tempphoneblock,0,$rows['shownum']);
				$endphone = array();
				foreach($tempphone as $row){
					$endphone[]= $row['name'];
				}
				$subname = implode(" ",$endphone);
				$category[]['category'] = array(
					"id" => $key,
					"name" => $rows['category'],
					"subname" => $subname
				);
			}			
			$this->data[$this->xmlRoot] = &$category;
		}
		$this->output();
	}
	
	
	
	public function iphoneplaceAction()
	{
		$this->xmlRoot = 'place';
		$lon = (float)$this->getRequest()->getParam('lon', 0);
		$lat = (float)$this->getRequest()->getParam('lat', 0);
		
		if ($lon && $lat) {
			$tmp = Better_LL::parse($lon, $lat);
			$lon = $tmp['lon'];
			$lat = $tmp['lat'];
			
			$geo = new Better_Service_Geoname();
			$geoInfo = $geo->getGeoName($lon, $lat);
			
			$address = '';
			if (trim($geoInfo['r1']) && trim($geoInfo['r2'])) {
				$address = str_replace('{NO1}', $geoInfo['r1'], $this->lang->poi->place->string);
				$address = str_replace('{NO2}', $geoInfo['r2'], $address);
			}

			$this->data[$this->xmlRoot] = $this->api->getTranslator('place')->translate(array(
				'lon' => $lon,
				'lat' => $lat,
				'address' => $address,
				'city' => $geoInfo['name'],
				));			
		}		
		
		$this->output();
	}
}