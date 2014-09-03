<?php

/**
 * 黑莓时光3 
 * http://k.ai/poi/1144131
 * 10次无效签到可以入手红色版勋章
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Berrytimes1 extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];
		$score = (float)$params['score'];
		
		if ($uid && $poiId==1144131) {
			if ($score>0) {
				$result = true;
			} else {
				$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'user_place_log', array(
					new Zend_Db_Expr('COUNT(*) AS total')
					));
				$select->where('uid=?', $uid);
				$select->where('score<=?', 0);
				$rs = self::squery($select, $rdb);
				$row = $rs->fetch();
				$total = $row['total'];
				
				if ($total>=10) {
					$result = true;
				}
			}
		}
		
		return $result;
	}
}