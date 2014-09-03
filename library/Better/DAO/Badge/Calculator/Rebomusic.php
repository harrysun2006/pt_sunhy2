<?php

/**
勋章名称
 热波音乐节
 
获得条件
 签到“保利198公园 http://k.ai/poi?id=14557349”+至少2个第三方同步
 
上线时间
 4月22日10:00
 
下线时间
 5月3日24:00
 

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Rebomusic extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 4, 22, 2011);
		$endtm = gmmktime(16, 0, 0, 5, 3, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==14557349){						
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