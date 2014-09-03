<?php

/**

  百年老店
签到名称含以下关键词：内联升、马聚源、青云阁、瑞蚨祥、亨得利、张一元、六必居、正明斋、大观楼


 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingbainianlaodian extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		$keylist  = array('内联升','马聚源','青云阁','瑞蚨祥','亨得利','张一元','六必居','正明斋','大观楼');
		foreach($keylist as $row){
			if(preg_match('/'.$row.'/', $poiInfo['name'])){
				$result = true;
				break;
			}
		}			
		return $result;
	}
}
?>