<?php

/**
KAMA LOVE
签到http://k.ai/poi/19074822并至少同步至一个第三方

即时
2011年6月7日5：00


 *

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Kamalove extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);	
		$begtm = gmmktime(2,0,0,5,16,2011);
		$endtm = gmmktime(21,0,0,6,6,2011);	
		$now = time();
		if($now>=$begtm && $now<=$endtm && $poiId==19074822){
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
		}		
		return $result;
	}
}