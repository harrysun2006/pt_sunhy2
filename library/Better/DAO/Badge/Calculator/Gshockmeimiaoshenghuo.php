<?php

/**
 G-SHOCK
签到任一指定CASIO专柜，并附照片，并至少同步一个第三方；或者在以下POI中的吼吼、贴士中含有照片，并至少同步一个第三方；
POI:
http://k.ai/poi?id=19078558
http://k.ai/poi?id=17307657
http://k.ai/poi?id=19078561
http://k.ai/poi?id=19078562
http://k.ai/poi?id=19078565

即时
6月19日 24:00

 *
 */
class Better_DAO_Badge_Calculator_Gshockmeimiaoshenghuo extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$blog = &$params['blog'];
		$poiId = (int)$params['poi_id'];	
		$begtm = gmmktime(5, 50, 0, 6, 9, 2011);
		$endtm = gmmktime(18, 0, 0, 6, 19, 2011);
		$poilist = array(19078558,17307657,19078561,19078562,19078565);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist) && $blog['attach']) {
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