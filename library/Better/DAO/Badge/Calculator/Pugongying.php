<?php

/**
 * 勋章名称
 蒲公英
 
获得条件
 绑定了腾讯微博
 
上线时间
 即时
 
下线时间
 无
 
描述语
 您成功绑定了腾讯微博。
 
同步语
 我获得了开开k.ai〖蒲公英〗勋章。愿我的足迹追随着蒲公英到这个世界的各个美好角落：）
 

 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Pugongying extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);

		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);

		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));		
		$select->where('uid=?', $uid);
		$select->where('protocol=?', 'qq.com');
		Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
		$rs = self::squery($select, $rdb);
		$row = $rs->fetch();
		
		if ($row['total']==1) {
			$result = true;
		}
		
		return $result;
	}
}