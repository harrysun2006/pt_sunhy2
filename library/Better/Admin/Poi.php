<?php
/**
 * admin页面 POI操作
 * @author  yangl
 */

class Better_Admin_Poi{
	
	/**
	 * 获得一些POI
	 */
	public static function getPOIs($params=array()){
		$return = array(
			'count' => 0,
			'rows' => array(),
		);
		
		$uid = Better_Registry::get('sess')->admin_uid;
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$namekeyword = $params['namekeyword'] ? trim($params['namekeyword']) : '';
		$poi_id = $params['poi_id'] ? trim($params['poi_id']) : '';
		$placekeyword = $params['placekeyword'] ? trim($params['placekeyword']) : '';
		$from = $to = '';
		$reload = $params['reload'] ? 1 : 0;
		$poi_from = $params['poi_from']? $params['poi_from'] :'';
		$doubt = $params['doubt']? $params['doubt']:'';
		$dyna = $params['dyna']? $params['dyna']:'';
		$city_lon = $params['city_lon'] ? $params['city_lon'] : '';
		$city_lat = $params['city_lat'] ? $params['city_lat'] : '';
		$range = $params['range'] ? $params['range'] : 999999999; // deprecated
		
		if ($params['from']) {
			$from = $params['from'];
			$y = substr($from, 0, 4);
			$m = substr($from, 5, 2);
			$d = substr($from, 8, 2);	
			$from = gmmktime(0, 0, 0, $m, $d, $y)-BETTER_8HOURS;
		}
		
		if ($params['to']) {
			$to = $params['to'];
			$y = substr($to, 0, 4);
			$m = substr($to, 5, 2);
			$d = substr($to, 8, 2);	
			$to = gmmktime(23, 59, 59, $m, $d, $y)-BETTER_8HOURS;
		}
		
		if ( ($namekeyword || $placekeyword) && (!$from && !$to && !$poi_id) ) {
			if($namekeyword && !$placekeyword){
				$keyword = $namekeyword;
			}else{
				$keyword = 'more:('.$namekeyword.' '.$placekeyword.')';
			}
			
			if($city_lon=='' || $city_lat==''){
				$city_lon = 116.397;
				$city_lat = 39.917;
				$range = 999999999;
			}
			
			$poiParams = array(
				'what' => 'poi',
				'keyword'=>$keyword,
				'page' => $page,
				'count' => $pageSize,
				'lon'=> $city_lon,
				'lat'=> $city_lat,
				'range'=> $range,
				'method' => 'fulltext',
			);
      if(isset($params['radius']) && is_numeric($params['radius']))
        $poiParams['radius'] = intval($params['radius']);

			//$result = Better_Admin_Search_Poi::getInstance('fulltext')->getPois($poiParams);
			$result = Better_Search::factory($poiParams)->search();
			$return['count']=$result['total'];
		} else {
			$namekeyword = $placekeyword = '';
			
			$poiParams = array(
				'page' => $page,
				'from' => $from,
				'to' => $to,
				'namekeyword' => $namekeyword,
				'poi_id' =>$poi_id,
				'placekeyword' => $placekeyword,
				'reload' => $reload,
				'pageSize' => $pageSize,
				'poi_from'=> $poi_from,
				'doubt'=> $doubt,
				'dyna'=> $dyna
			);
			
			$result = Better_Admin_Search_Poi::getInstance('mysql')->getPois($poiParams);
			$return['count']=$result['count'];
		}
		
		
		
		
		$categories = Better_DAO_Admin_Poi::getInstance()->getCategoties();
		foreach($result['rows'] as $row){
			/*if($row['major']){
				$major = Better_User::getInstance($row['major'])->getUserInfo();	
				if($major['uid']){
					$row['major_name'] =  $major['username'];
				}
			}
			
			if($row['creator']){
				$creator = Better_User::getInstance($row['creator'])->getUserInfo();
				if($creator['uid']){
					$row['creator_name'] =  $creator['username'];
				}
			}*/
			$row['category_name'] = $categories[$row['category_id']];
			
			$return['rows'][]= $row;
		}
		
		return $return;
	}
	
	
	/**
	 * 后台删除POI
	 */
	public static function deletePOI(array $poids=array()){
		$result = 0;
		if($poids && count($poids)>0){
			foreach ($poids as $poid){
			$poi = Better_Poi_Info::getInstance($poid)->getBasic();
			
				if(!$poi['ref_id']){
					Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addPoiLog($poid, 'close_poi', '关闭POI');
					
					Better_DAO_Poi_Fulltext::getInstance()->updateItem($poid, 2);
					
					Better_DAO_Admin_Poi::getInstance()->closePOI($poi) && $result = 1;
				}else{
					/*Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addPoiLog($poid, 'merge_poi', '合并POI:<br>'.$poid.'=>'.$poi['ref_id']);
					
					Better_DAO_Poi_Fulltext::getInstance()->updateItem($poid, 2);
					Better_DAO_Poi_Fulltext::getInstance()->updateItem($poi['ref_id'], 1);
					
					Better_DAO_Admin_Poi::getInstance()->mergePOI($poi, $poi['ref_id']) && $result = 1;	*/
					$result = Better_Admin_Simipoi::mergePOI($poi['poi_id'], $poi['ref_id']);
				}
				
			}
		}
		return $result;
	}
	
	/**
	 * 后台修改poi
	 */
	public static function updatePOI(array $data, $val){
		if($data && is_array($data) && $val){
				
			$poi = Better_Poi_Info::getInstance($val)->getBasic();
			list($x, $y) =  Better_Functions::LL2XY($poi['lon'], $poi['lat']);
			
			$logarray = array();
			if($data['category_id']!=$poi['category_id']){
				$logarray[] = '类别：'.$poi['category_id'].'=>'.$data['category_id'];
			}
			if($data['name']!=$poi['name']){
				$logarray[] = '名称：'.$poi['name'].'=>'.$data['name'];
			}
			if($data['city']!=$poi['city']){
				$logarray[] = '城市：'.$poi['city'].'=>'.$data['city'];
			}
			if($data['address']!=$poi['address']){
				$logarray[] = '地址：'.$poi['address'].'=>'.$data['address'];
			}
			if($data['phone']!=$poi['phone']){
				$logarray[] = '电话：'.$poi['phone'].'=>'.$data['phone'];
			}
			if($data['label']!=$poi['label']){
				$logarray[] = 'label：'.$poi['label'].'=>'.$data['label'];
			}
			if($data['intro']!=$poi['intro']){
				$logarray[] = '介绍：'.$poi['intro'].'=>'.$data['intro'];
			}
			if($data['certified']!=$poi['certified']){
				$logarray[] = '认证：'.$poi['certified'].'=>'.$data['certified'];
			}
			if($data['closed']!=$poi['closed']){
				$logarray[] = '关闭：'.$poi['closed'].'=>'.$data['closed'];
			}
			if($data['forbid_major']!=$poi['forbid_major']){
				$logarray[] = '禁止掌门：'.$poi['forbid_major'].'=>'.$data['forbid_major'];
			}
			if($data['level_adjust']!=$poi['level_adjust']){
				$logarray[] = 'level_adjust：'.$poi['level_adjust'].'=>'.$data['level_adjust'];
			}
			if($data['x']!=$x || $data['y']!=$y){
				list($lo, $la) = Better_Functions::XY2LL($data['x'], $data['y']);
				$logarray[] = '经纬度：('.$poi['lon'].', '.$poi['lat'].')=>('.$lo.', '.$la.')';
			}
			
			$result = Better_DAO_Admin_Poi::getInstance()->update($data, $val);
			
			if(Better_Registry::get('sess') && Better_Registry::get('sess')->admin_uid){
				$admin_uid = Better_Registry::get('sess')->admin_uid;
			}else{
				$admin_uid = '100';
			}
			
			//log
			$logmsg = '修改POI基本信息：<br>';
			foreach($logarray as $row){
				$logmsg .= $row.'<br>';
			}
			
			Better_Admin_Administrators::getInstance($admin_uid)->addPoiLog($val, 'update_poi_info', $logmsg);
			
			if($data['closed']==1){
				Better_DAO_Poi_Fulltext::getInstance()->updateItem($val, 2);
			}else{
				Better_DAO_Poi_Fulltext::getInstance()->updateItem($val, 1);
			}
			
			return true;
		}else{
			return false;
		}
	}
	
	public static function updatePoiOwner($data, $val){
		if($data && $val){	

			$dataa = array(
			'ownerid' =>$data
			);
			$result = Better_DAO_Admin_Poi::getInstance()->update($dataa, $val);

			/*	
			Better_DAO_Poi_Owner::getInstance()->delete(array(
	 			'poi_id'=> $val
	 			));
	 		//Better_Log::getInstance()->logInfo($data,'updatepoi');
	 		$owner = split('\,',$data);
	 		Better_Log::getInstance()->logInfo(serialize($owner),'updatepoi');
	 		if(!is_array($owner)){
	 			$result = Better_DAO_Poi_Owner::getInstance()->insert(array(
		 			'poi_id'=> $val,
					'owner_id'=> (int)$owner,
		 			'dateline' => BETTER_NOW
	 			));	
	 			Better_Log::getInstance()->logInfo("结果：".$result,'updatepoi');	
	 		} else {
				foreach($owner as $row){	
					//Better_Log::getInstance()->logInfo($row,'updatepoi');			
					$row>0 && $result=Better_DAO_Poi_Owner::getInstance()->insert(array(
			 			'poi_id'=> $val,
						'owner_id'=> (int)$row,
			 			'dateline' => BETTER_NOW
		 			));	
		 				//Better_Log::getInstance()->logInfo("结果：".$result,'updatepoi');	
				}	
	 		}
	 		*/
			if(Better_Registry::get('sess') && Better_Registry::get('sess')->admin_uid){
				$admin_uid = Better_Registry::get('sess')->admin_uid;
			}else{
				$admin_uid = '100';
			}
			Better_DAO_Poi_Fulltext::getInstance()->updateItem($val, 1);
			Better_Admin_Administrators::getInstance($admin_uid)->addPoiLog($val, 'update_poi_info', '修改POI店主');
			
			return true;
		}else{
			return false;
		}
	}
	public static function clearPoiOwner($val){
		if($val){			
			Better_DAO_Poi_Owner::getInstance()->delete(array(
	 			'poi_id'=> $val
	 			));
			if(Better_Registry::get('sess') && Better_Registry::get('sess')->admin_uid){
				$admin_uid = Better_Registry::get('sess')->admin_uid;
			}else{
				$admin_uid = '100';
			}
			Better_Admin_Administrators::getInstance($admin_uid)->addPoiLog($val, 'update_poi_info', '清除POI店主');
			Better_DAO_Poi_Fulltext::getInstance()->updateItem($val, 1);
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 获得所有的POI分类
	 */
	public static function getPOICategory(){
		return Better_DAO_Admin_Poi::getInstance()->getCategoties();
	}
	
	
	/**
	 * 后台重置POI皇帝
	 */
	public static function resetMajor(array $poids=array()){
		
		if($poids && count($poids)>0){
			foreach ($poids as $poid){
				$poi = Better_Poi_Info::getInstance($poid)->getBasic();
				if($poi['major']){
					Better_DAO_Admin_Poi::getInstance()->resetMajor($poid, $poi['major']);
					
					Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addPoiLog($poid, 'reset_poi_major', '重置POI掌门');
					
					Better_DAO_Poi_Fulltext::getInstance()->updateItem($poid, 1);
				}
			}
			return true;
		}
		else{
			return false;
		}
		
	}
	
	
	/**
	 * 建立一个POI与另一个POI的联系
	 */
	public static function refPOI($params){
		$result = false;
		$poi_id = $params['poi_id'] ? $params['poi_id']: '';
		$ref_poi_id = $params['ref_poi_id'] ? $params['ref_poi_id']: '';
		
		if($poi_id && $ref_poi_id){
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addPoiLog($poi_id, 'ref_poi', '设置要合并进的POI:<br>'.$poi_id.'=>'.$ref_poi_id);
			
			$result =  Better_DAO_Admin_Poi::getInstance()->refPOI($poi_id, $ref_poi_id);
		}
		
		return $result;
	}
	
	
	/**
	 * 建立一堆POI与另一个POI的联系
	 */
	public static function refMutiPOI($params){
		$result = false;
		$pids = $params['pids'] ? $params['pids']: '';
		$target_poi_id = $params['target_poi_id'] ? $params['target_poi_id']: '';
	 	
		if(is_array($pids)){
			if($pids && $target_poi_id){
				$admin_poi = Better_DAO_Admin_Poi::getInstance();
				foreach($pids as $pid){
					$admin_poi->refPOI($pid, $target_poi_id);
				}
				$result = true;
			}		
		}
		
		return $result;
	}
	public static function getSpecial(array $params){
		$return = array(
			'count' => 0,
			'rows' => array(),
		);
		
		$uid = Better_Registry::get('sess')->admin_uid;
		
		try{
		$result=Better_DAO_Admin_Poi::getInstance()->getAllSpecial($params);
		}catch(Exception $e){die($e);}
	
		$return['count']=$result['count'];
		
		foreach($result['rows'] as $row){
		
			
			if($row['image_url']){
				if(preg_match('/^([0-9]+).([0-9]+)$/', $row['image_url']))	{
					$attach = Better_Attachment_Parse::getInstance($row['image_url'])->result();
					$row['attach_tiny'] = $attach['tiny'];
					$row['attach_thumb'] = $attach['thumb'];
					$row['attach_url'] = $attach['url'];	
				} else if (preg_match('/^http(.+)$/', $row['image_url'])) {
					$row['attach_tiny'] = $row['attach_thumb'] = $row['attach_url'] = $row['image_url'];
				}
			}		
			switch($row['checked']){
				case '0':
					$row['check_type'] = '未审核';
					break;
				case '1':
					$row['check_type'] = '已审核';
					break;
				case '2':
					$row['check_type'] = '审核不通过';
					break;
				case '4':
					$row['check_type'] = '用户取消';
					break;
				case '5':
					$row['check_type'] = '过期了';
					break;		
			}
			
			$return['rows'][]= $row;
		}
		
		return $return;
	}
	
	
	public static function checkSpecial($params){
		if(isset($params['updategroup']) && $params['updategroup']==1){
			$result = Better_DAO_Admin_Poi::getInstance()->checkallSpecial($params);
		} else {
			$result = Better_DAO_Admin_Poi::getInstance()->checkSpecial($params);
		}
		return $result;
	}
	public static function checkallSpecial($params){
		$result = Better_DAO_Admin_Poi::getInstance()->checkallSpecial($params);
				
		return $result;
	}
	public static function updateSpecial($params){
		$result = Better_DAO_Admin_Poi::getInstance()->updateSpecial($params);
		
		return $result;
	}
	public static function newSpecial($params){
		$result = Better_DAO_Admin_Poi::getInstance()->newSpecial($params);
		
		return $result;
	}
	public static function getAllpoisUpdatedbyuser($page)
	{
		$results = Better_DAO_Admin_Poicheckupdate::getInstance()->getAll($page);
		return $results;
	}
	public static function getAllpoisBySearch($sets)	
	{
		$results = Better_DAO_Admin_Poicheckupdate::getInstance()->getPoisBySearch($sets);
		return $results;
	}
	public static function getNameByCategroyId($c_id)
	{
		$categroies = Better_DAO_Admin_Poi::getInstance()->getCategoties();
		return $categroies[$c_id];
	}
	
	public static function getUpdateById($id)
	{
		$result = Better_DAO_Admin_Poicheckupdate::getInstance()->getById($id);
		return $result;
	}
	public static function updateCheckpoi($id,$status)
	{
		$result=0;
		if($id){
			$result = Better_DAO_Admin_Poicheckupdate::getInstance()->updateCheckPOI($id,$status);
		}		
		return $result;
	}
	/**
	 * 参数可能是数组也可能是单个Int型，可以何在一起处理也可以分开处理，这里是分开处理
	 * @param  $id   the id will be deleted in the table
	 */
	public static function deleteCheckpoi($id)
	{
		if(is_array($id)){
			if(count($id)>0){
				foreach ($id as $key=>$value){
					$check = Better_DAO_Admin_Poicheckupdate::getInstance()->getById($value);
					$result_ref = Better_DAO_Admin_Poicheckupdate::getInstance()->deleteCheckPOI($value);
					if($result_ref){
						if(Better_Registry::get('sess') && Better_Registry::get('sess')->admin_uid){
							$admin_uid = Better_Registry::get('sess')->admin_uid;
						}else{
							$admin_uid = '100';
						}
						$logmsg = '审核:删除用户的更新POI的请求';
						$logmsg .= '<br> check id 为'.$value;
						Better_Admin_Administrators::getInstance($admin_uid)->addPoiLog($check['poi_id'], 'delete_check_record', $logmsg);				
					}
				}
				$result=$result_ref?"1":"0";
			}
			return $result;
		}
		if($id){
			$check = Better_DAO_Admin_Poicheckupdate::getInstance()->getById($id);
			$result_ref = Better_DAO_Admin_Poicheckupdate::getInstance()->deleteCheckPOI($id);
			if($result_ref){
				if(Better_Registry::get('sess') && Better_Registry::get('sess')->admin_uid){
					$admin_uid = Better_Registry::get('sess')->admin_uid;
				}else{
					$admin_uid = '100';
				}
				$logmsg = '审核:删除用户的更新POI的请求';
				$logmsg .= '<br> check id为'.$id;				
				Better_Admin_Administrators::getInstance($admin_uid)->addPoiLog($check['poi_id'], 'delete_check_record', $logmsg);				
			}
		}
		$result=$result_ref?"1":"0";
		return $result;
	}
}


?>
