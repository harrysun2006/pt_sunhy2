<?php

/**
湖畔映像
签到“独墅湖影剧院http://k.ai/poi/135690”+至少2SNS
4月1日（五）
4月2日（六）24:00

 *

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Hupanyinxiang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begintm = gmmktime(16, 0, 0, 3, 31, 2011);
		$endtm = gmmktime(16, 0, 0, 4, 2, 2011);
		$now = time(); 
		if($now>=$begintm && $now<=$endtm && $poiId==135690){
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