<?php

/**

  京味儿

东华门小吃206341 +九门小吃4424528 +地安门小吃4301081+簋街184100(任意2处)

 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingjingweier extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);					
		$poilist = array(206341,4424528,4301081,184100);		
		if(in_array($poiId,$poilist)){
			$temppoi = array();
			foreach($poilist as $row){
				if($row!=$poiId) {
					$temppoi[] = $row;
				}
			}
			$poistr =implode(",", $temppoi);			
			$sql = "select count(*) as t_count from better_user_place_log as log where uid=".$uid." and checkin_score>0 and  poi_id in (".$poistr.")  group by poi_id";	
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$rs = Better_DAO_Base::squery($sql, $rdb);			
			$data = $rs->fetchAll();
			if(count($data)>=1){
				$result = true;	
			}							
		}
		return $result;
	}
}
?>