<?php

/**
 * 李公堤勋章数据
 * 在李公堤签到过三次
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Ligongdi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$range = '11249932,3074756,11248160,3074092';
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$rgs = array();
		$ranges = explode('|', $range);
		foreach ($ranges as $v) {
			list($x1, $y1, $x2, $y2) = explode(',', $v);
			
			$rgs[] = " MBRWithin(GeomFromText('POINT(".$params['x'].' '. $params['y'].")'), GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')) AND MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
		}				
		$where = new Zend_Db_Expr(implode(' OR ', $rgs));
		
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'user_place_log', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $uid);
		$select->where($where);
		$select->where('checkin_score>0');
		
		Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
		$rs = self::squery($select, $rdb);
		$row = $rs->fetch();
		
		$row['total']>=3 && $result = true;
		
		return $result;
	}
}