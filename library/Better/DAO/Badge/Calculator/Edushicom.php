<?php

/**
勋章名 E都市 

同步语 我获得了开开〖E都市〗勋章！ 
上下线时间确定 上线：6月29日上午10点 下线：8月31日晚24点 
获得条件 签到天堂软件园 http://k.ai/poi?id=326345 ，并在吼吼中包含"E都市“三个字+输入兑换码 

 
Edushicom

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Edushicom extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 6, 29, 2011);
		$endtm = gmmktime(24, 0, 0, 8, 31, 2011);
		$now = time();		
	
		if($now>=$begtm && $now<=$endtm && $poiId==326345){	
				
			$blog = &$params['blog'];						
			if ($blog['type']=='normal' || $blog['type']=='checkin') {
				$message = strtolower($blog['message']);
				$checked1 = '/e都市/';	
							
				if (preg_match($checked1, $message)) {
				
					$edushi['page_size'] = 500;
					$edushi['uid'] = 0;
					$data = Better_DAO_Edushi::getInstance()->getunused();	
					
					foreach($data as $row){	
						$idnum = "/".$row['id']."/";
						$id = $row['id'];				
						if (preg_match($idnum, $message)){
							$_data['uid'] = $uid;
							$_data['dateline'] = time();
							$a = Better_DAO_Edushi::getInstance()->update($_data, $id);	
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