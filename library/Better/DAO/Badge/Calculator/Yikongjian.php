<?php

/**
勋章名称
 壹空间
 
获得条件
 签到壹空间http://k.ai/poi?id=19060920并同步至一个第三方
 
上线时间
 2011年4月26日
 
下线时间
 2012年4月26日
 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yikongjian extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 4, 25, 2011);
		$endtm = gmmktime(16, 0, 0, 4, 26, 2012);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==19060920){
					
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