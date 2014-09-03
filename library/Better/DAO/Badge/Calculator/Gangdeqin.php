<?php

/**
签到24家指定影院并在签到的同时说出“钢的琴”，或者在24个地点的吼吼含有“钢的琴”，并且至少同步至新浪微博
2011年7月15日9：00
2011年7月25日 24:00


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Gangdeqin extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(1, 0, 0, 7, 15, 2011);
		$endtm = gmmktime(16, 0, 0, 7, 25, 2011);
		$poilist = array(248128,125679,79027,78419,124963,5383583,78147,19058719,19085663,78762,442368,125637,504040,78146,78233,125493,77978,79064,124916,19077463,249005,19085665,504034,504031);
		
		$now = time();		
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
			$checked = false;
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);	
			$select->where('protocol=?', 'sina.com');			
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=1) {				
				$checked = true;			
			}			
			$blog = &$params['blog'];						
			if ($checked && ($blog['type']=='normal' || $blog['type']=='checkin')) {
				$message = strtolower($blog['message']);				
				$checked = array('/钢的琴/')	;
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