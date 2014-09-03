<?php

/**
 *擦主席

获得条件：开开用户实地签到以下POI+至少1个同步第三方，即日起至12月30日24点结束：

         木马剧场-擦主席个展&新书签售会 http://k.ai/poi/1364607

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Chairmanca extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$end = gmmktime(16, 0, 0, 12, 30, 2010);
		$now = time();		
		if($now<=$end && $poiId==1364607){
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