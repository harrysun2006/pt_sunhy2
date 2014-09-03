<?php

/**
 勋章名	天堂隔壁
描述	是疯人还是天使？和梅婷一起在“天堂隔壁”疯吧！1月21日24时前，在苏州科文中心签到并绑定任一同步SNS，即可获此虚拟勋章
同步语	和梅婷一起在“天堂隔壁”疯吧！《天堂隔壁是疯人院》 科文中心倾情上演！10年经典，全新演绎！
上下线时间确定	即日起至2011年1月21日24点
获得条件	签到  http://k.ai/poi/131572
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Tiantanggebi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{		
		parent::touch($params);
		$result = false;
		$config = Better_Config::getAppConfig();
		$range = $config->app_store->badge->range;
		
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$end = gmmktime(16, 0, 0, 1, 21, 2011);
		$now = time();	
		
		if ($now<=$end && $poiId==131572) {
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