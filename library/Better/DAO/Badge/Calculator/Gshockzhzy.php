<?php

/**

签满全部指定5个CASIO POI,并同步任一SNS。POI ID: 上海名店运动城港汇店 4085235  
上海正大广场专卖店 19084486  
上海龙之梦购物中心 19084488  
上海第一八佰伴 4110046  
上海久光百货 122773 
7月9日10点至7月19日24点

7月12日鞠文琦要求只要签任意2个，其他条件不变
 * @package Better.DAO.Badge.Calculator
 * @author hanc <hanc@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Gshockzhzy extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);	
		$begtm = gmmktime(2, 0, 0, 7, 9, 2011);
		$endtm = gmmktime(16, 0, 0, 7, 19, 2011);
		$now = time();		
		$poilist = array(4085235,19084486,19084488,4110046,122773);
		if((($now>=$begtm && $now<=$endtm) || $uid==1318647) && in_array($poiId,$poilist)){			
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);		
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=1) {
				$temppoi = array();
				foreach($poilist as $row){
					if($row!=$poiId) {
						$temppoi[] = $row;
					}
				}
				$poistr =implode(",", $temppoi);			
				$sql = "select * from better_user_place_log as log where uid=".$uid." and checkin_score>0 and  poi_id in (".$poistr.")  group by poi_id";	
				$rs = Better_DAO_Base::squery($sql, $rdb);			
				$data = $rs->fetchAll();
				if(count($data)>=1){
					$result = true;	
				}		
			}					
		}
		return $result;
	}
}
?>