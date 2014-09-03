<?php

/**
 * 修复一些用户计数
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

//	进程锁
define('SYNC_BLOG_LOCK', dirname(__FILE__).'/fix_counters.lock');

//	标定环境为Cron
define('IN_CRON', true);
 
 // 定义Better路径
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

function killLock() 
{ 
	file_exists(SYNC_BLOG_LOCK) && unlink(SYNC_BLOG_LOCK); 
}

set_time_limit(0);
//error_reporting(0);
date_default_timezone_set('UTC');
 
// 检测是否有同步锁
file_exists(SYNC_BLOG_LOCK) && exit(0);
 
//	没有同步锁则继续执行同步操作
register_shutdown_function('killLock');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require_once 'Better/Cache.php';
require_once 'Better/Cache/Handler/Base.php';
require_once 'Better/Loader.php';
Better_Loader::getInstance()->register();

Better_Config::load();
$appConfig = Better_Config::getAppConfig();

//	定义常量，减少array_key_exists调用
define('BETTER_NOW', time());
define('BETTER_BASE_URL', Better_Config::getAppConfig()->base_url);
define('BETTER_DB_TBL_PREFIX', Better_Config::getDbConfig()->global->tbl_prefix);
define('BETTER_SYS_UID', $appConfig->user->sys_user_id);

define('BETTER_DB_DEBUG', Better_Config::getDbConfig()->global->debug==1 ? true : false);
define('BETTER_ENABLE_LOG', $appConfig->log->enable==1 ? true : false);
define('BETTER_QBS_DEBUG', $appConfig->qbs->debug==1 ? true : false);

define('BETTER_PAGE_SIZE', $appConfig->blog->page_size);
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);
define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);

define('PHP_EXE', Better_Config::getAppConfig()->php_exe);




























$sess = Better_Session::factory();
$sess->init();

$sids = Better_DAO_User_Assign::getInstance()->getServerIds();


$cityArray[0] = array("北京市","北京");
$cityArray[1] = array("上海市","上海");
$cityArray[2] = array("天津市","天津");
$cityArray[3] = array("重庆市","重庆");
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
$citybugstr = "贾汪区*徐州*吉利区*洛阳*达州市*达州*河口区*东营*石龙区*平顶山*白云矿区*包头*南岳区*衡阳*遂宁市*遂宁*巴中市*巴中*资阳市*资阳";
$citybug = split("\*",$citybugstr);	
$k = 0;
foreach($sids as $sid) {
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
	$pois = Better_DAO_Base::registerDbConnection('poi_server');
	$rdb = &$cs['r'];
	$wdb = &$cs['w'];
	$rpoisdb = &$pois;
        $sql = "SELECT last_checkin_poi as poi_id,uid FROM better_profile where last_checkin_poi!=0 and (live_province IS  NULL or live_city is null)  order by last_active desc";
        //$sql = "SELECT last_checkin_poi as poi_id,uid FROM better_profile where 1 order by last_active desc";
        $rs = Better_DAO_Base::squery($sql, $rdb);
        $rows = $rs->fetchAll();
        $whatcity = array();
        foreach ($rows as $row) {
        	//	好友数
        	$uid = $row['uid'];
        	
			$poi_id = $row['poi_id'];
			$sql = "SELECT x(xy) as x,y(xy) as y FROM better_poi WHERE poi_id='".$poi_id."'";
			$rs = Better_DAO_Base::squery($sql, $rpoisdb);			
			$data = $rs->fetch();			
			$tempdate = Better_Functions::XY2LL($data['x'], $data['y']);			
			$geo = new Better_Service_Geoname();		
			$info = $geo->getBigcityName($tempdate[0], $tempdate[1]);
			$thecityname = $info[0]['name'];
			$hadfind = 1;
			for($i=0;$i<count($cityArray);$i++){
				$temppro = $cityArray[$i][0];
				for($j=0;$j<count($citybug);$j++){
					if($thecityname==$citybug[$i]){
						$thecityname = 	$citybug[$i+1];
						break 1;
					}
				}
				$temp_city = array();	
				$temp_city = split("\|",$cityArray[$i][1]);				
				for($j=0;$j<count($temp_city);$j++){
					$a =strpos($thecityname,$temp_city[$j]);				
					if($a === false)
					{
						
					} else {
						$theproname = $temppro;
						$thecityname = $temp_city[$j];
						$sql = "UPDATE better_profile SET live_province='".$theproname."', live_city='".$thecityname."'  WHERE uid='".$uid."'";
			Better_Log::getInstance()->logInfo($thecityname."--**--".$sql,'xxa');
			//Better_DAO_Base::squery($sql, $wdb);
                        echo $sql, ";", "\r\n";
						$hadfind = 0;
						break;
					}
				}
			}
       		if($hadfind && strlen($thecityname)!=0 && !in_array($thecityname,$whatcity)){
       			
       			$whatcity[] = $thecityname;								
			}
			if($hadfind || strlen($thecityname)==0){
				Better_Log::getInstance()->logInfo($uid."--".$thecityname,'notfind');
				$theproname = $thecityname = '未知';
				$sql = "UPDATE better_profile SET live_province='".$theproname."', live_city='".$thecityname."'  WHERE uid='".$uid."'";	
				//Better_DAO_Base::squery($sql, $wdb);
                                echo $sql, ";", "\r\n";			
			}
			$k++;
       	 	//echo $k."--".$uid."\n";
		}
		Better_Log::getInstance()->logInfo(serialize($whatcity),'cantfind');		
}

//echo "Done.\n";
exit(0);
