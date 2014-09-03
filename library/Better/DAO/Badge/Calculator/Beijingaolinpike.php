<?php

/**

  奥林匹克
签到国家体育场（鸟巢）、国家游泳中心（水立方）、奥林匹克公园其中任1处：126952、127356、352231


 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingaolinpike extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);			
		$poilist = array(126952,127356,352231);		
		if(in_array($poiId,$poilist)){			
			$result = true;					
		}
		return $result;
	}
}
?>