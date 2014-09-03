<?php

/**
 *至鱼眼儿咖啡http://k.ai/poi/1271264，照片上传满3张
 * 2011.2.28，24:00pm
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Ontheroad extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$blog = &$params['blog'];
		$poiId = (int)$params['poi_id'];	
		$begin = gmmktime(16, 0, 0, 1, 20, 2011);	
		$end = gmmktime(16, 0, 0, 2, 28, 2011);
		$now = time();
		if ($poiId==1271264 && $now<=$end && $blog['attach']) {
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);	
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));
			$select->where('uid=?', $uid);
			$select->where('poi_id=?', $poiId);
			$select->where('dateline>=?', $begin);
			$select->where('dateline<=?', $end);
			$select->where('attach!=?', '');
			
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=3) {
				$result = true;
			}
		}
		
		return $result;
	}
}