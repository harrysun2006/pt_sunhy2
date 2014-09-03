<?php

/**
 * 雷锋精神
 * 你发布了100条贴士 
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Leifengjingshen extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$blog = &$params['blog'];
		$uid = $params['uid'];
		$abUid = Better_Config::getAppConfig()->user->aibang_user_id;
		
		if ($blog['type']=='tips' && $uid!=$abUid) {
			$uid = (int)$params['uid'];
			$user = Better_User::getInstance($uid);
	
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
	
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));
			$select->where('uid=?', $uid);
			$select->where('type=?', 'tips');
			
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();
			
			if ($row['total']>=100) {
				$result = true;
			}
		}
		
		return $result;
	}
}