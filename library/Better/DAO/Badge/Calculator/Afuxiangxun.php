<?php

/**
勋章名 阿芙香薰 
描述 5月5日至31日，签到阿芙香薰专柜@北京  就可凭签到在专柜领取护理+精品体验装！32家专柜通用！ 
同步语 我获得了开开〖阿芙香薰〗勋章！签到阿芙香薰专柜@北京 即刻领取护理+精品体验装！32家专柜通用！ 
上下线时间确定 5月5日上午11点~5月31日24点 
勋章兑换 兑换提示：兑换面部护理/体验装
兑换成功语：你已成功兑换AFU阿芙面部护理/体验装一份！ 
兑换次数：每人一次  
兑换密码：AFU88
 
获得条件 签到附件列表中的POI+同步一个SNS 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Afuxiangxun extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(3, 0, 0, 5, 5, 2011);
		$endtm = gmmktime(16, 0, 0, 5, 31, 2011);
		$now = time();		
		$poilist = array(767015,6892582,19069618,6903563,19069623,19069627,19069628,6906147,6896943,6898094,19071222,19071225,19071232,19071235,19071236,19071237,19071268,19071270,19071278,19071280,19071298,19071300,5406389,19071306,19071405,19071406,19071409,4293872,19071412,19071413,19071415,19071417,19071424);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
					
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