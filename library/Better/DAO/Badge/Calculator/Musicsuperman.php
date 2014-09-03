<?php

/**
 * 勋章名 音乐超人 
描述 2011.05.15 王力宏合肥演唱会，化身音乐超人！签到本地次数最多的前2名将各获得演唱会门票2张；签到并吼吼 “爱力宏”的内容，就能参与抽奖赠送王力宏签名海报10张。 
同步语 我获得了开开〖音乐超人〗勋章！2011.05.15 王力宏合肥演唱会，化身音乐超人！ 
上下线时间确定 上线：5月10日上午10:30 ;下线：5月15日24点 (banner也同时下线） 
获得条件 签到http://k.ai/poi?id=9786057 +至少一个同步 
勋章类别 品牌优惠 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Musicsuperman extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 30, 0, 5, 10, 2011);
		$endtm = gmmktime(16, 0, 0, 5, 15, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==9786057){	
					
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