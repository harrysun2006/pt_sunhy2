<?php

/**
 * emoi基本生活
 *签到emoi基本生活20家门店POI中任意一个+至少新浪微博同步 
*门店POI详见附件表格
2011.4.2，即时
2011.5.9

上海 SHANGHAI
818广场店
http://k.ai/poi?id=19050223
金桥广场店
http://k.ai/poi?id=19050228
上海大宁国际店
http://k.ai/poi?id=19050229

北京 BEIJING
来福士广场店
http://k.ai/poi?id=816287
朝阳大悦城店
http://k.ai/poi?id=19065134
翠微广场店
http://k.ai/poi?id=19065135
西单大悦城店
http://k.ai/poi?id=18991044
富力广场店
http://k.ai/poi?id=19065140
中关村家乐福
http://k.ai/poi?id=19065143

沈阳 SHENYANG
恒隆广场店
http://k.ai/poi?id=19065144

深圳 SHENZHEN
华侨城店
http://k.ai/poi?id=1076148
海岸城店
http://k.ai/poi?id=19065146
KK MALL店
http://k.ai/poi?id=19065147
COCO PARK店
http://k.ai/poi?id=19065148

广州 GUANGZHOU
东方宝泰广场店
http://k.ai/poi?id=589975
中华广场店
http://k.ai/poi?id=19065150
广州万菱汇店
http://k.ai/poi?id=19065151

成都 CHENGDU
仁恒置地广场店
http://k.ai/poi?id=19065153
春熙路店
http://k.ai/poi?id=19065154

杭州 HANGZHOU
万象城店
http://k.ai/poi?id=19065155



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Emoilife extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];		
		$poi_list = array(19050223,19050228,19050229,816287,19065134,19065135,18991044,19065140,19065143,19065144,1076148,19065146,19065147,19065148,589975,19065150,19065151,19065153,19065154,19065155);
		$start = gmmktime(16, 0, 0, 4, 1, 2011);
		$end = gmmktime(16, 0, 0, 5, 9, 2011);
		$now = time(); 
		if ($now<=$end && in_array($poiId,$poi_list)) {
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);	
			$select->where('protocol=?', 'sina.com');	
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=1) {
				$result = true;
			}			
		}

		return $result;
	}
	
}