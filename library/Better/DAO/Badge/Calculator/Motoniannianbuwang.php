<?php

/**
签到以下（见附件列表）POI+任一同步+吼吼“#MOTO呼朋引伴#@……我想和你一起”

说明：@……为任意名字
 
上线时间
 7月15日即时
 
下线时间
 8月16日0:00am
 

 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Motoniannianbuwang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(6, 0, 0, 7, 15, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 15, 2011);
		$poilist = array(123048,19086204,19078497,124078,19081631,19086206,126708,19086207,19049615,10420547,761354,19085976,556727,19086211,19086212,11394149,19086214,19085387,19086216,10423482);
		
		$now = time();		
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
			$checked = false;
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);		
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=1) {				
				$checked = true;			
			}			
			$blog = &$params['blog'];						
			if ($checked && ($blog['type']=='normal' || $blog['type']=='checkin')) {
				$message = strtolower($blog['message']);				
				$checked = array('/moto呼朋引伴/','/@/','/我想和你一起/')	;
				$total = 0;
				foreach($checked as $row){
					if (preg_match($row, $message)){			
						$total = $total +1;			
					} else {
						break;
					}
				}
				if($total==count($checked)){
					$result = true;
				}
			}					
		}			
		
		return $result;
	}
	
	
	
}