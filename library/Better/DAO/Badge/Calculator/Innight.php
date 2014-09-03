<?php

/**
 * In Night勋章
 * 获得条件：开开用户实地签到以下任意POI+至少1个同步第三方
http://k.ai/poi/19053534
http://k.ai/poi/4364267
http://k.ai/poi/4362317
http://k.ai/poi/356028
http://k.ai/poi/630025
http://k.ai/poi/11309175
 * 
 * In Night勋章，即日起到2月10号24时，在以下地点签到并绑定任一同步，就能获得，请你尽快上线，谢谢！
 
  
星光现场  1363852  
人民大会堂 78150  
五棵松体育馆 86596  
13CLUB  560734  
中央美术学院美术馆 4361030  
北京展览馆剧场 840684  
国家奥林匹克体育中心 17984252  
微薄之盐  4365858  
天桥剧场  299120  
CN Club  19055172  
Suzie Wong  18497662  
东方先锋剧场 6827225  
The House  19055174 

热力猫俱乐部 19055171
江湖酒吧  555722  
两个好朋友 4366204  
愚公移山  4364267  
D-22  18497518  
Mao Live House 19055170  
麻雀瓦舍  19052974  
School  17349369  
木马剧场  1364607 


签到以下POI+同步1个第三方，获得【In Night】勋章，截止1月31日(时间跟施岚确认了，可以到2月10日)
http://k.ai/poi/612715,369668,256375,4471310,85808,10799153,4243237,10799153,4205411,326243,19055496
http://k.ai/poi/369668
http://k.ai/poi/256375
http://k.ai/poi/4471310
http://k.ai/poi/85808
http://k.ai/poi/10799153
http://k.ai/poi/4243237  
http://k.ai/poi/10799153
http://k.ai/poi/4205411
http://k.ai/poi/326243
http://k.ai/poi/19055496

时间有变更（截止到1月31日24时）

新一期（从上线起至本月28日24:00）
怀柔琉璃庙 		19058628
当代唐人艺术中心		4362055
枫蓝国际小剧场		19007671
国家图书馆音乐厅		19053747
北京地质礼堂剧场		78146
国家大剧院		4349651
北京音乐厅		78590
唐会T.too club		4365074
蜂巢剧场		326384
保利剧院		78817
东方先锋剧场		6827225
木马剧场		1364607
Mao Live House		19055170
愚公移山		4364267
江湖酒吧		555722



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Innight extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];		
		$poi_list1 = array(19055170,555722,4365858,560734,957483,4363571,19064416,4364267,560677,19060924,19060920,19071724,1363852,15723870,19052974);			
		$begtm1 = gmmktime(16, 0, 0, 4, 30, 2011);
		$endtm1 = gmmktime(16, 0, 0, 5, 31, 2011);	
		$poi_list2 = array(19053534);
		$begtm2 = gmmktime(16, 0, 0, 5, 17, 2011);
		$endtm2 = gmmktime(16, 0, 0, 5, 22, 2011);		
		$now = time(); 
		if (($now>=$begtm1 && $now<=$endtm1 && in_array($poiId,$poi_list1)) || ($now>=$begtm2 && $now<=$endtm2 && in_array($poiId,$poi_list2))) {
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