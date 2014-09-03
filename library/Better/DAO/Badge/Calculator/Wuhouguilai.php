<?php

/**
描述 蔡依林myself演唱会巅峰之作，5月21日引爆上海虹口足球场演唱会 璀璨见证舞娘蜕变奇迹，赢得最惊艳的目光！截止5月19日上午10点，签到次数最多的2名用户各获得门票2张。凭签到即可至上海永乐票务实地兑换纪念明信片！  
同步语 我获得了开开〖舞后归来〗勋章！蔡依林myself演唱会巅峰之作，5月21日引爆上海虹口足球场演唱会！ 
上下线时间确定 上线：5月16日上午10点;下线:5月21日24:00（banner也同时上、下线） 
获得条件 签到http://k.ai/poi?id=8118604 并至少同步一个SNS 

 *

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Wuhouguilai extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);	
		$begtm = gmmktime(2,0,0,5,16,2011);
		$endtm = gmmktime(16,0,0,5,21,2011);	
		$now = time();
		if($now>=$begtm && $now<=$endtm && $poiId==8118604){
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