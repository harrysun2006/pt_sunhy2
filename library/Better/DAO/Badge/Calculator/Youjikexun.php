<?php

/**
所有勋章获得时间都是6/10 10:00~7/11 0:00，但四个勋章对应的Poi 不同：

可获得玫瑰谷勋章的poi有：
6898094,19071222,19071225,19071232,19071235,19071236,19071237,19071268
 

 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Youjikexun extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(2, 0, 0, 9, 5, 2011);
		$sql = "select date_format(from_unixtime(checkin_time+8*3600),'%Y') as year,date_format(from_unixtime(checkin_time+8*3600),'%m') as month,date_format(from_unixtime(checkin_time+8*3600),'%d') as day from better_user_place_log where uid=".$uid." and checkin_time>=".$begtm ." and checkin_score>0 group by date_format(from_unixtime(checkin_time+8*3600),'%Y %m %d') order by checkin_time";
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);		
		$rs = Better_DAO_Base::squery($sql, $rdb);
		$rows = $rs->fetchAll();		
		if (count($rows)>=21){			
			for($i=0;$i<count($rows);$i++){
				$tempi = $i+20;
				if(isset($rows[$tempi]['year'])){
					$begtm = gmmktime(2,0,1,$rows[$i]['month'],$rows[$i]['day'],$rows[$i]['year']);
					$endtm = gmmktime(2,0,1,$rows[$tempi]['month'],$rows[$tempi]['day'],$rows[$tempi]['year']);				
					$result = $begtm+20*24*3600==$endtm ? true : false;								
					if($result){
						break;
					}
				} else {
					continue;
				}			
			}
		}		
		return $result;
	}
}