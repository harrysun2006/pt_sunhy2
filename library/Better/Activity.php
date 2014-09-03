<?php

/**
 * 
 * 活动
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Activity
{
	
	public static function getAll(array $params=array())
	{
		$cacher = Better_Cache::remote();
		$cacheKey = 'market_activities';
		$result = $cacher->get($cacheKey);
		
		if (!$result) {
			$rows = &Better_DAO_Activity::getInstance()->getActivities($params);
			$data = array();
			
			if (count($rows)>0) {
				foreach ($rows as $row) {
					$row['poi'] = $row['poi_id'] ? Better_Poi_Info::getInstance($row['poi_id'])->getBasic() : array();
					$data[] = $row;
				}
			}
			
			$cacher->set($cacheKey, $data);
		}
		
		return $result;
	}
	
	public static function getCityRecommends(array $params)
	{
		$page = $params['page'] ? (int)$params['page'] : 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$city = trim($params['city']);
		$uid = (int)$params['uid'];
		
		$result = array(
			'rows' => array(),
			'count' => 0
			);
			
		if (APPLICATION_ENV=='production') {
			
		} else {
			$result = Better_Poi_Tips::recommends(array(
				'uid' => $uid,
				'page' => 1,
				'page_size' => 3
				));
		}
		
		return $result;
	}
	
	public function getAllactivity($params){
		$return = array(
			'count' => 0,
			'rows' => array(),
		);
		
		$uid = Better_Registry::get('sess')->admin_uid;
		
		try{
		$result=Better_DAO_Activity::getInstance()->getAllactivity($params);
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
					$row['check_type'] = '线下';
					break;
				case '1':
					$row['check_type'] = '线上';
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
	
	public function create($params)
	{
		$result = array();
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1
			);
		$result['codes'] = &$codes;
		$code = $codes['FAILED'];
		$act_id = Better_DAO_Activity::getInstance()->getMaxid()+1;
		
		$activityToInsert = array(
				'act_id' => $act_id,				
				'uid' => $params['creator'],				
				'title' => $params['title'],
				'content' => $params['content'],				
				'checked' => 0,	
				'dateline' => time(),	
				'begintm' => $params['begintm'],
				'endtm' => $params['endtm'],		
				);
	
		$flag = Better_DAO_Activity::getInstance()->insert($activityToInsert);
	
		if ($flag) {
			$code = $codes['SUCCESS'];
			
		}
		
		$result['code'] = $code;
		$result['act_id'] = $act_id;
		return $result;
	}
	
	public function updateimg($params){
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1
			);
		$result['codes'] = &$codes;
		$code = $codes['FAILED'];
	
		
		$activityupdate = array(
				'act_id' => $params['act_id'],				
				'image_url' => $params['image_url'],
				'attach_url' => $params['attach_url']		
				);
	
		$flag = Better_DAO_Activity::getInstance()->update($activityupdate,$params['act_id']);
	
	}
}