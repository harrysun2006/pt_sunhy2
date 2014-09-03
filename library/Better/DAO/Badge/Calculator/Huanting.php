<?php

/**
勋章名
 幻听
 
勋章分类
 精彩活动
 
获得条件
 签到“愚公移山http://k.ai/poi/4364267”+至少2个第三方同步 
 
上线时间
 6月9日（四）
 
下线时间
 6月18日2:00am（六）
 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Huanting extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(5, 50, 0, 6, 9, 2011);
		$endtm = gmmktime(18, 0, 0, 6, 17, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==4364267){						
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);		
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=2) {				
					$result = true;			
			}			
		}
		return $result;
	}
}