<?php

/**
鲍勃迪伦
签到以下任一POI+至少2个SNS：
北京工人体育馆http://k.ai/poi/19066318

上海大舞台http://k.ai/poi/326373
2011年4月6日即时
2011年4月9日1：00am






 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Bobdylan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$endtm = gmmktime(17, 0, 0, 4, 8, 2011);
		
		$now = time();		
		if($now<=$endtm & ($poiId==19066318 || $poiId==326373)){					
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