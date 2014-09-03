<?php

/**
 * 假日记忆
 * 国庆期间你发表了5张以上的图片
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Memoryofholiday extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$blog = &$params['blog'];
		
		$start = gmmktime(16, 0, 0, 9, 30, 2010);
		$end = gmmktime(16, 0, 0, 10, 7, 2010);
		$now = time();
		
		if ($now>=$start && $now<=$end && $blog['attach']) {
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
	
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));
			$select->where('uid=?', $uid);
			$select->where('attach!=?', '');
			$select->where('dateline>='.$start.' AND dateline<='.$end);
			
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();
			
			if ($row['total']>=5) {
				$result = true;
			}
		}
		
		return $result;
	}
}