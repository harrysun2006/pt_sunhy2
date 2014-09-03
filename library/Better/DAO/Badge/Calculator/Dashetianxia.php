<?php

/**
 * 大赦天下
 * 你发表了10张以上的图片
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Dashetianxia extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$blog = &$params['blog'];
		
		if ($blog['attach']) {
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
	
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));
			$select->where('uid=?', $uid);
			$select->where('attach!=?', '');
			
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();
			
			if ($row['total']>=10) {
				$result = true;
			}
		}
		
		return $result;
	}
}