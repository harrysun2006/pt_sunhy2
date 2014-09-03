<?php

/**
吼吼“哇噻网新版上线”或“wowsai新版上线”+ 任一序号（1、2、3、4、5、6）+ 至少2个同步
8月1日即时
 
下线时间
 10月1日0:00am
 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Wowsaicom extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 8, 1, 2011);
		$endtm = gmmktime(16, 0, 0, 9, 30, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm){	
			$blog = &$params['blog'];		
			$syncnums = Better_User_Syncsites::getInstance($uid)->getSites();				
			if ($blog['type']=='normal' && count($syncnums)>=2) {
				$message = strtolower($blog['message']);											
				$checkinfo1 = array('/哇噻网新版上线/','/wowsai新版上线/');			
				$checkinfo2 = array('/1/','/2/','/3/','/4/','/5/','/6/');
				foreach($checkinfo1 as $row){						
					if (preg_match($row, $message)){
						$result = true;	
						break;
					}	
				}
				if($result){
					$result = false;
					foreach($checkinfo2 as $row){						
						if (preg_match($row, $message)){
							$result = true;	
							break;
						}	
					}	
				}					
			}						
		}
		return $result;
	}
}