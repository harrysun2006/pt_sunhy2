<?php

/**
 * 观前街勋章数据
 * 在观前街发表过5条以上贴士
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Guanqianjie extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$range = '11243472,3075860,11242702,3074936';
		
		$blog = &$params['blog'];
		$uid = $params['uid'];
		$abUid = Better_Config::getAppConfig()->user->aibang_user_id;
		
		if ($blog['type']=='tips' && $uid!=$abUid) {
			
			$uid = (int)$params['uid'];
			$user = Better_User::getInstance($uid);
			
			$rgs = array();
			$ranges = explode('|', $range);
			foreach ($ranges as $v) {
				list($x1, $y1, $x2, $y2) = explode(',', $v);
				
				$rgs[] = " MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
			}				
			$where = new Zend_Db_Expr(implode(' OR ', $rgs));
			
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));
			$select->where('uid=?', $uid);
			$select->where('type=?', 'tips');
			$select->where($where);
			
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
			
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();
			
			$row['total']>5 && $result = true;
		}
		
		return $result;
	}
}