<?php

/**
 * 
 * 亚运会勋章
 * 10.12-11.27开开用户在以下21个城市指定POI实地签到满5个城市即可获得
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yayunhui extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$start = gmmktime(16, 0, 0, 10, 11, 2010);
		$end = gmmktime(16, 0, 0, 11, 26, 2010);
		$now = time();
		
		if ($now>=$start && $now<=$end) {
			$poiIds = array(
				32482,		//	中山市：孙中山故居纪念馆http://k.ai/poi/32482
				560077,		//	珠海市：珠海渔女http://k.ai/poi/560077
				1546481,	//	东莞市：林则徐销烟池与虎门炮台旧址http://k.ai/poi/1546481
				650619,		//	深圳市：深圳世界之窗http://k.ai/poi/650619
				1392573,	//	惠州市：罗浮山风景名胜区http://k.ai/poi/1392573
				1547785, 	//	汕尾市：凤山祖庙旅游区http://k.ai/poi/1547785
				572782,		//	汕头市：礐石风景名胜区http://k.ai/poi/572782
				911043,		//	揭阳市：广东黄岐山森林公园http://k.ai/poi/911043
				1548941,	//	潮州市：湘子桥http://k.ai/poi/1548941
				1548981,	//	梅州市：梅州千佛塔http://k.ai/poi/1548981
				1549180,	//	河源市：赵佗故城--佗城http://k.ai/poi/1549180
				565920,		//	韶关市：丹霞山风景名胜区http://k.ai/poi/565920
				1549239,	//	清远市：广东第一峰旅游风景区http://k.ai/poi/1549239
				1549339,	//	肇庆市：七星岩风景区http://k.ai/poi/1549339
				1549879,	//	云浮市：国恩寺http://k.ai/poi/1549879
				1552002,	//	茂名市：茂名放鸡岛海上游乐世界http://k.ai/poi/1552002
				1552138,	//	湛江市：硇洲灯塔http://k.ai/poi/1552138
				1552402,	//	阳江市：大角湾风景名胜区http://k.ai/poi/1552402
				1552437,	//	江门市：开平碉楼http://k.ai/poi/1552437
				1321684,	//	佛山市：黄飞鸿纪念馆http://k.ai/poi/1321684
				1546049		//	广州市：广州电视塔http://k.ai/poi/1546049 
				);
			$uid = $params['uid'];
			$poiId = $params['poi_id'];
			
			if ($uid && in_array($poiId, $poiIds)) {
				$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
				$sql = "SELECT COUNT(DISTINCT(poi_id)) AS total
				FROM ".BETTER_DB_TBL_PREFIX."user_place_log
				WHERE checkin_score>0 AND poi_id IN ('".implode("','", $poiIds)."') AND uid='".$uid."'
				";
				$rs = self::squery($sql, $rdb);
				$row = $rs->fetch();
				$total = (int)$row['total'];
				
				if ($total>=2) {
					$result = true;
				}
			}
		}
		
		return $result;
	}
	
}