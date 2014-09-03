<?php
/**
 * Created on 2009-10-20
 * 
 * @package    Better.Server.GeoName
 * @author     FengJun <fengj@peptalk.cn>
 * @modifier	leip <leip@peptalk.cn>
 */

class Better_Service_Geoname
{
	public static function getLonLat($city)
	{
		return Better_DAO_Geoname::getInstance()->getLonLat($city);
	}
	
	//根据经纬度得到相关地理信息 失败返回false;
	//成功返回数组：code 地区编码，name 城市名称，r1 r2 两个街道，level 坐标离街道的距离
	public function getGeoName($lon,$lat)
	{
		$level = array(1000,1500,2000);
		
		foreach ($level as $level_value){
			$rows = Better_DAO_Geoname::getInstance()->getGeoname($lon, $lat, $level_value);
			
			$i = 0;
			foreach ($rows as $row) {
				if ($i==0) {
					$distance_init = self::getDistance($lon,$lat,$row['lon'],$row['lat']);
					$return_info = $row;
				} else {
					$distance = self::getDistance($lon,$lat,$row['lon'],$row['lat']);
					if ( $distance < $distance_init ) $return_info = $row;
				}				
				$i ++;
			}
			
			if ($i>0) {
				return $return_info + array('level' => $level);
			}
			continue;
		}
		return false;
	}
	
	
	public function getCityName($lon, $lat){

		$cityname = Better_DAO_Geoname::getInstance()->getCityname($lon, $lat, 2000);		
		return $cityname;
	}
	
	public function getBigcityName($lon,$lat){
		$cityname = Better_DAO_Geoname::getInstance()->getBigcityname($lon, $lat, 2000);		
		return $cityname;
	}
	
	public static function getDistance($lon1,$lat1,$lon2,$lat2)
	{
		$PI = pi();
		$R = 6.3781e6 ;
		$x = ($lon2-$lon1)*$PI*$R*cos( (($lat1+$lat2)/2) *$PI/180)/180;
		$y = ($lat2-$lat1)*$PI*$R/180;
		$out = hypot($x,$y)/1000;
		return $out; 
	}	
	
	/**
	 * 得到省份
	 */
	public function getProvinceByCity($cityname)
	{
		$cityArray = self::cityData();
		foreach ($cityArray as $values) {
			list($province, $_city) =  $values;
			$a_citynames = explode('|', $_city);
			foreach ($a_citynames as $s_cityname) {
				if (strpos($cityname, $s_cityname) !== false ) {
					return $province;
				}
			}
		}
		
		return '江苏省';
	}
	
	public static function bedoProvince($province)
	{
		$cityData = self::cityData();
		foreach ($cityData as $key=>$val) {
			if (strpos($val['0'], $province) !== false) {
				return array($key, $val['0']);
			}
		}
		return null;
	}
	
	public static function bedoCity($index, $city)
	{
		$cityData = self::cityData();
		$citys = $cityData[$index];
		if ($citys) {
			$cityArray = explode('|', $citys);
			foreach ($cityArray as $val) {
				if ($val == $city) {
					return $val;
				}
			}
			return $cityArray['0'];
		}
		return null;
	}
	
	private static function cityData()
	{
		$cityArray = array();
		$cityArray[0] = array("北京市","北京市");
		$cityArray[1] = array("上海市","上海市");
		$cityArray[2] = array("天津市","天津市");
		$cityArray[3] = array("重庆市","重庆市");
		$cityArray[4] = array("河北省","石家庄|邯郸|邢台|保定|张家口|承德|廊坊|唐山|秦皇岛|沧州|衡水");
		$cityArray[5] = array("山西省","太原|大同|阳泉|长治|晋城|朔州|吕梁|忻州|晋中|临汾|运城");
		$cityArray[6] = array("内蒙古自治区","呼和浩特|包头|乌海|赤峰|通辽|鄂尔多斯|呼伦贝尔|巴彦淖尔|乌拉察布|兴安盟|锡林郭勒盟|阿拉善盟");
		$cityArray[7] = array("辽宁省","沈阳|大连|鞍山|抚顺|本溪|丹东|锦州|营口|阜新|辽阳|盘锦|铁岭|朝阳|葫芦岛");
		$cityArray[8] = array("吉林省","长春|吉林|四平|辽源|通化|白山|松原|白城|延边");
		$cityArray[9] = array("黑龙江省","哈尔滨|齐齐哈尔|牡丹江|佳木斯|大庆|绥化|鹤岗|鸡西|黑河|双鸭山|伊春|七台河|大兴安岭");
		$cityArray[10] = array("江苏省","南京|镇江|苏州|南通|扬州|盐城|徐州|连云港|常州|无锡|宿迁|泰州|淮安");
		$cityArray[11] = array("浙江省","杭州|宁波|温州|嘉兴|湖州|绍兴|金华|衢州|舟山|台州|丽水");
		$cityArray[12] = array("安徽省","合肥|芜湖|蚌埠|马鞍山|淮北|铜陵|安庆|黄山|滁州|宿州|池州|淮南|巢湖|阜阳|六安|宣城|亳州");
		$cityArray[13] = array("福建省","福州|厦门|莆田|三明|泉州|漳州|南平|龙岩|宁德");
		$cityArray[14] = array("江西省","南昌市|景德镇|九江|鹰潭|萍乡|新馀|赣州|吉安|宜春|抚州|上饶");
		$cityArray[15] = array("山东省","济南|青岛|淄博|枣庄|东营|烟台|潍坊|济宁|泰安|威海|日照|莱芜|临沂|德州|聊城|滨州|菏泽");
		$cityArray[16] = array("河南省","郑州|开封|洛阳|平顶山|安阳|鹤壁|新乡|焦作|濮阳|许昌|漯河|三门峡|南阳|商丘|信阳|周口|驻马店|济源");
		$cityArray[17] = array("湖北省","武汉|宜昌|荆州|襄樊|黄石|荆门|黄冈|十堰|恩施|潜江|天门|仙桃|随州|咸宁|孝感|鄂州");
		$cityArray[18] = array("湖南省","长沙|常德|株洲|湘潭|衡阳|岳阳|邵阳|益阳|娄底|怀化|郴州|永州|湘西|张家界");
		$cityArray[19] = array("广东省","广州|深圳|珠海|汕头|东莞|中山|佛山|韶关|江门|湛江|茂名|肇庆|惠州|梅州|汕尾|河源|阳江|清远|潮州|揭阳|云浮");
		$cityArray[20] = array("广西壮族自治区","南宁|柳州|桂林|梧州|北海|防城港|钦州|贵港|玉林|南宁地区|柳州地区|贺州|百色|河池");
		$cityArray[21] = array("海南省","海口|三亚");
		$cityArray[22] = array("四川省","成都|绵阳|德阳|自贡|攀枝花|广元|内江|乐山|南充|宜宾|广安|达川|雅安|眉山|甘孜|凉山|泸州|遂宁|巴中|资阳|阿坝");
		$cityArray[23] = array("贵州省","贵阳|六盘水|遵义|安顺|铜仁|黔西南|毕节|黔东南|黔南");
		$cityArray[24] = array("云南省","昆明|大理|曲靖|玉溪|昭通|楚雄|红河|文山|思茅|西双版纳|保山|德宏|丽江|怒江|迪庆|临沧");
		$cityArray[25] = array("西藏自治区","拉萨|日喀则|山南|林芝|昌都|阿里|那曲");
		$cityArray[26] = array("陕西省","西安|宝鸡|咸阳|铜川|渭南|延安|榆林|汉中|安康|商洛");
		$cityArray[27] = array("甘肃省","兰州|嘉峪关|金昌|白银|天水|酒泉|张掖|武威|定西|陇南|平凉|庆阳|临夏|甘南");
		$cityArray[28] = array("宁夏回族自治区","银川|石嘴山|吴忠|固原");
		$cityArray[29] = array("青海省","西宁|海东|海南|海北|黄南|玉树|果洛|海西");
		$cityArray[30] = array("新疆维吾尔族自治区","乌鲁木齐市|克拉玛依市|昌吉|石河子市|博尔塔拉|伊犁|阿勒泰|塔城|哈密|和田|阿克苏|克孜勒苏|喀什|五家渠|阿拉尔|吐鲁番|图木舒克|巴音郭楞");
		$cityArray[31] = array("香港特别行政区","香港特别行政区");
		$cityArray[32] = array("澳门特别行政区","澳门特别行政区");
		$cityArray[33] = array("台湾省","台北|高雄|台中|台南|屏东|南投|云林|新竹|彰化|苗栗|嘉义|花莲|桃园|宜兰|基隆|台东|金门|马祖|澎湖");
		$cityArray[34] = array("Overseas","Europe|North America|South America|Asia|Africa|Oceania");
		$cityArray[35] = array("未知", "未知");	
		return $cityArray;
	}
		
	/**
	 * 
		<locationinfo><citycode>320683</citycode><city>江苏省 南通市 通州市</city><road1>通甲路</road1><road2>通州市通甲路</road2><address>江苏省 南通市 通州市通甲路（通州市通甲路 附近）</address></locationinfo>
	 * @param $lon
	 * @param $lat
	 * @return unknown_type
	 */
	public function getAddress($lon, $lat)
	{
		$geoInfo = array(
						'citycode' => '', 
						'name' => '',
						'r1' => '',
						'r2' => '',
						);
		
		//http://lbs.org.cn/api/lbs/geo.php?k=49667eeb6faf9343cc313362c508c13e&v=2&c=32.0183~120.9255
		$ch = curl_init();
		$url = Better_Config::getAppConfig()->lbs->server->url . "/api/lbs/geo.php?k=49667eeb6faf9343cc313362c508c13e&v=2&c=$lat~$lon";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		$strxml = curl_exec($ch);
		$info = curl_getinfo($ch);
		if ( $info['http_code'] != 200 ) {
			error_log('LBS GEO:' . curl_error($ch));
			return $geoInfo;
		}
		
		$xml = new DOMDocument('1.0', 'utf-8');
		$ok  = @$xml->loadXML($strxml);
		
		$locationinfo = $xml->getElementsByTagName('locationinfo')->item(0);
		if ($locationinfo->getAttribute('err') != 0) {
			return $geoInfo;
		}
		
		$citycode = $locationinfo->getElementsByTagName("citycode")->item(0)->nodeValue;
		$city = $locationinfo->getElementsByTagName("city")->item(0)->nodeValue;
		$road1 = $locationinfo->getElementsByTagName("road1")->item(0)->nodeValue;
		$road2 = $locationinfo->getElementsByTagName("road2")->item(0)->nodeValue;		
		$address = $locationinfo->getElementsByTagName("address")->item(0)->nodeValue;	

		$address = str_replace($city, '', $address);
		
		$geoInfo = array(
						'citycode' => $citycode, 
						'name' => $city,
						'r1' => $road1,
						'r2' => $road2,
						'address' => $address,
						);		
			
		return $geoInfo;
	}
}