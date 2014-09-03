<?php

/**
 * 亚运会
 * 获得条件：

11.12-11.27，任意地点[吼吼]任意内容，凡包含“亚运”二字，并同步至少1个绑定网站，即可获得。

 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Asiangame extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$uid = (int)$params['uid'];		
		$blog = &$params['blog'];	
		$start = gmmktime(16, 0, 0, 11, 11, 2010);
		$end = gmmktime(16, 0, 0, 11, 27, 2010);
		$now = time();				
		if ($now<=$end && $now>=$start && $blog['type']=='normal' ){	
			$message = $blog['message'];			
			if(preg_match('/亚运/', $message)){						
				$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);	
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
					new Zend_Db_Expr('COUNT(*) AS total')
					));		
				$select->where('uid=?', $uid);
				Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
				$rs = self::squery($select, $rdb);
				$row = $rs->fetch();			
				if ($row['total']>=1) {
					$result = true;
				}	
			}
		}
		return $result;
	}
}