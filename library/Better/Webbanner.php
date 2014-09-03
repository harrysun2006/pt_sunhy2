<?php

/**
 * 
 * 网站Banner
 * 
 * @package Better
 * @author hanc <hanc@peptalk.cn>
 *
 */

class Better_Webbanner
{
	
	public static function getAll(array $params=array())
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
		);
		
		$uid = Better_Registry::get('sess')->admin_uid;
		
		try{
		$result = &Better_DAO_Webbanner::getInstance()->getAll($params);
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
		$id = Better_DAO_Webbanner::getInstance()->getMaxid()+1;
		
		$bannerToInsert = array(
				'id' => $id,					
				'link' => $params['link'],				
				'checked' => 0,	
				'rank' => $params['rank'],	
				'begintm' => $params['begintm'],
				'endtm' => $params['endtm'],		
				);
	
		$flag = Better_DAO_Webbanner::getInstance()->insert($bannerToInsert);
	
		if ($flag) {
			$code = $codes['SUCCESS'];
			
		}
		
		$result['code'] = $code;
		$result['id'] = $id;
		return $result;
	}
	
	public function updateimg($params){
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1
			);
		$result['codes'] = &$codes;
		$code = $codes['FAILED'];	
		Better_Log::getInstance()->logInfo(serialize($params),'webbanner');
		$bannerupdate = array(
				'id' => $params['id'],				
				'imageurl' => $params['imageurl']
				);	
		$flag = Better_DAO_Webbanner::getInstance()->update($bannerupdate,$params['id']);
	
	}
}