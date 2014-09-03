<?php

class Better_Admin_Log
{
	
	public static function getAll(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$user_keyword = $params['user_keyword'] ? trim($params['user_keyword']) : '';
		$act_type = $params['act_type'] ? trim($params['act_type']) : '';

		$reload = $params['reload'] ? 1 : 0;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$from = $to = '';
		
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

		$rows = Better_DAO_Admin_Log::getInstance()->getAll(array(
			'page' => $page,
			'from' => $from,
			'to' => $to,
			'keyword' => $keyword,
			'user_keyword' => $user_keyword,
			'act_type' => $act_type,
			'reload' => $reload,
			'page_size' => $pageSize,
			));

		return $rows;			
	}
	
	public function deleteExtralogs($type,$postion,$max)
	{
		$logFolder = Better_Config::getAppConfig()->filter->words_log;
		if($type>0 && $type<5) {
			$partten = "/^words?$type-?$postion/";
		}else if ($type == 'poi'){
			$partten ="/^poi-words-/";
		}
	
		$filenamesArr = array();
		$fileToDel = array();
		if(is_dir($logFolder)){
			if($dh= opendir($logFolder)){
				while (($file = readdir($dh)) !== false)
				{		
					if(filetype($logFolder . $file) == 'file' && preg_match($partten,$file)){				
						array_push($filenamesArr,$file);
						if(count($filenamesArr)>$max){
							array_push($fileToDel,array_shift($filenamesArr));
						}
					}
				}
				foreach($fileToDel as $del){
					$fullpath = $logFolder.$del;
					if(file_exists($fullpath)){
						unlink($fullpath);
					}
				}
			closedir($dh);
			}
		}
	}
	
	public static function backupWords($type,$words1="",$words2="")
	{
		switch($type){
			case 1:
				$fPath1 = Better_Config::getAppConfig()->filter->words1.'-1.txt';
				$fPath2 = Better_Config::getAppConfig()->filter->words1.'-2.txt';
				$type_string = '禁止发表关键词';		
				break;
			case 2:
				$fPath1 = Better_Config::getAppConfig()->filter->words2.'-1.txt';
				$fPath2 = Better_Config::getAppConfig()->filter->words2.'-2.txt';	
				$type_string = '替换发表关键词';				
				break;
			case 3:
				$fPath1 = Better_Config::getAppConfig()->filter->words3.'-1.txt';
				$fPath2 = Better_Config::getAppConfig()->filter->words3.'-2.txt';	
				$type_string = '审核发表关键词';				
				break;
			case 4:
				$fPath1 = Better_Config::getAppConfig()->filter->words4.'-1.txt';
				$fPath2 = Better_Config::getAppConfig()->filter->words4.'-2.txt';	
				$type_string = '发表审核关键词';				
				break;
			case 'poi':
				$fPath1 = Better_Config::getAppConfig()->filter->poi_words;
				$fPath2 = false;
				$type_string = '禁止POI名称关键字';				
				break;
		}
		$logFolder = Better_Config::getAppConfig()->filter->words_log;
		$words1_log = @file_get_contents($fPath1);
		if($words1_log !=$words1){
			if($type=='poi'){
				$handle1_log = @fopen($logFolder.$type.'-words-'.date('Y-m-d').'.txt',"w");
			}else{
				$handle1_log = @fopen($logFolder.'words'.$type.'-1-'.date('Y-m-d').'.txt',"w");
			}
			$ok1_log = fwrite($handle1_log,$words1_log);
			@fclose($handle1_log);
			//日常增加备份操作，后台备份最近的10次修改。所谓一次修改，指的是：每天累计的修改的只算一次修改。
			self::deleteExtraLogs($type,1,10);
			$words1_arr_new =  explode("\r\n",$words1);
			$words1_arr_old   =explode("\r\n",$words1_log);
			$common=array_intersect($words1_arr_new,$words1_arr_old);
			$deleteData = array_diff($words1_arr_old,$common);
			$addData = array_diff($words1_arr_new,$common);
			$content = '修改<b>'.$type_string.'</b>第一个文本框:<br/>';
			if(!empty($addData)){
				$content .="&nbsp;&nbsp;新增了以下关键字:<br/>";
				foreach($addData as $aData){
					if(""!=$aData){
						$content .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$aData.'<br/>';
					}
				}
			}
			if(!empty($deleteData)){
				$content .="&nbsp;&nbsp;删除了以下关键字:<br/>";
				foreach($deleteData as $dData){
					if(""!=$dData){
						$content .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$dData.'<br/>';
					}
				}
			}		
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog($content, 'update_risk_words');		
		}
		if($fPath2){
			$words2_log = @file_get_contents($fPath2);
		}else{
			$words2_log=false;
		}
		if($words2_log && $words2_log != $words2){
			$handle2_log = @fopen($logFolder.'words'.$type.'-2-'.date('Y-m-d').'.txt',"w");
			$ok2_log = fwrite($handle2_log,$words2_log);
			fclose($handle2_log);
			//日常增加备份操作，后台备份最近的10次修改。所谓一次修改，指的是：每天累计的修改的只算一次修改。
			self::deleteExtraLogs($type,2,10);
			$words2_arr_new =  explode("\r\n",$words2);
			$words2_arr_old   =explode("\r\n",$words2_log);
			$common=array_intersect($words2_arr_new,$words2_arr_old);
			$deleteData = array_diff($words2_arr_old,$common);
			$addData = array_diff($words2_arr_new,$common);
			$content = '修改<b>'.$type_string.'</b>第二个文本框:<br/>';
			if(!empty($addData)){
				$content .="&nbsp;&nbsp;新增了以下关键字:<br/>";
				foreach($addData as $aData){
					if(""!=$aData){
						$content .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$aData.'<br/>';
					}
				}
			}
			if(!empty($deleteData)){
				$content .="&nbsp;&nbsp;删除了以下关键字:<br/>";
				foreach($deleteData as $dData){
					if(""!=$dData){
						$content .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$dData.'<br/>';
					}
				}
			}		
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog($content, 'update_risk_words');
		}	
	} 
}