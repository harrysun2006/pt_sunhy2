<?php

/**
 * 情人节
 * ：①签到科文并绑定SNS
②任意地点吼吼
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Qingrenjienew extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$uid = (int)$params['uid'];
		$poiId = (int)$params['poi_id'];
		$end = gmmktime(16, 0, 0, 2, 14, 2011);
		$now = time();		
		if ($now<=$end) {
			$blog = &$params['blog'];
			if($blog['type']=='checkin' && $poiId==131572){
				$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
					new Zend_Db_Expr('COUNT(*) AS total')
					));		
				$select->where('uid=?', $uid);		
				$rs = self::squery($select, $rdb);
				$row = $rs->fetch();			
				if ($row['total']>=1) {
					$result = true;
				}				
			} else if($blog['type']=='normal'){
				$message = $blog['message'];				
				if (preg_match('/情人节/', $message)) {
					$result = true;
				}
			}
		}		
		return $result;
	}
}