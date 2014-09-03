<?php

/**
 *〖热恋之钻〗 
上下线时间确定 上线：4月21日上午10:00，下线：2012年4月21日24:00 
获得条件 签到http://k.ai/poi?id=19068497 +至少同步到一个SNS 




 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Relianzhizuan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);	
		$begtm = gmmktime(2,0,0,4,21,2011);
		$endtm = gmmktime(16,0,0,4,21,2012);
		$now = time();				
		if($now>=$begtm && $now<=$endtm && $poiId==19068497){
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