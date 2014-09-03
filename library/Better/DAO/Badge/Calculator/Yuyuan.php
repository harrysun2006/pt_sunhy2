<?php

/**
 
勋章名 榆园 
描述 茶园和远山中的榆园，带给你清甜的“开胃”美景。 
同步语 我获得了开开【榆园】勋章，在暖暖阳光中畅享“开胃”美景~ 
上线时间 4月7日（周四）下午4点，下线时间无 
获得条件 在POI http://k.ai/poi?id=16687132 签到并绑定一个SNS 







 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yuyuan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		//$start = gmmktime(6, 0, 0, 4, 7, 2011);
		
		///$now = time();		
		if($poiId==16687132){					
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