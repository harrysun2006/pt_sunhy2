<?php

define('BETTER_START_TIME', microtime());

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

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
define('BETTER_MERGE_PAGE_SIZE', $appConfig->blog->merge_page_size);
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);
define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);
define('BETTER_LOG_TIME', $appConfig->log_time ? true : false);
define('BETTER_HASH_POI_ID', $appConfig->hash_poi_id ? true : false);
define('BETTER_VER_CODE', '2010051308');
$appConfig->in_testing ? define('BETTER_NEED_INVITECODE', true) : define('BETTER_NEED_INVITECODE', false);

$sess = Better_Session::factory();
$sess->init();

$ts = Better_Treasure::getAllTreasures();

header('Content-Type:text/html;charset=utf-8');

$uid = 174785;
$user = Better_User::getInstance($uid);
$friendsUids = $user->friends;
$followerUids = $user->followers;
$uids = array_intersect($friendsUids, $followersUids);
$uids = array_unique($uids);

var_dump($uids);
exit;
$x = '11247307';
$y = '3073790';
echo $x.'<HR>'.$y.'<HR>';
var_dump(Better_Functions::XY2LL($x, $y));exit;
  
echo "<style type='text/css'>
body {background:#aaa;}
</style>";

for($i=2;$i<=160;$i++) {
echo $i." : ";
echo "<img src='/images/badges/24/".$i.".png' />";
echo "<img src='/images/badges/36/".$i.".png' />";
echo "<img src='/images/badges/".$i.".png' />";
echo "<img src='/images/badges/48w/".$i.".png' />";
echo "<img src='/images/badges/96/".$i.".png' />";
echo "<img src='/images/badges/big/".$i.".png' />";
echo "<HR>";
}

exit(0);
$data = array();
foreach ($ts as $row) {
	$us = array();	
	$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
	foreach ($sids as $sid) {
		$cs = Better_DAO_User_Assign::assignDbConnection('user_server_'.$sid);
		$rdb = &$cs['r'];
			
		$select = $rdb->select();		
		$select->from(BETTER_DB_TBL_PREFIX.'user_treasures AS t');
		$select->join(BETTER_DB_TBL_PREFIX.'account AS a', 'a.uid=t.uid', array('a.uid', 'a.email'));
		$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=t.uid', array('p.username', 'p.nickname'));
		$select->where('t.treasure_id=?', $row['id']);
		
		$rs = $rdb->query($select);
		$tmp = $rs->fetchAll();
		foreach ($tmp as $a) {
			$us[] = $a;
		}
	}
	
	$data[count($us).'.'.$row['id']] = array(
		'treasure' => $row,
		'us' => $us
		);
}

krsort($data);
foreach ($data as $t) {
	$row = $t['treasure'];
	$us = $t['us'];
	echo '宝物ID：'.$row['id'].'，宝物名：'.$row['name'].'，共 ('.count($us).') 人<br />';
	
	foreach ($us as $row) {
		echo 'Uid:['.$row['uid'].'], Email:['.$row['email'].'], Username:['.$row['username'].'], Nickname:['.$row['nickname'].']'.'<br />';
	}
	
	echo '<HR>';
}
exit(0);

$lat = 31+17/60+53/3600;
$lon= 120+39/60+53/3600;

echo $lon.'<HR>'.$lat.'<HR>';
echo 'E'.$lon.'N'.$lat.'<HR>';
$tmp = Better_LL::parse($lon, $lat);
echo 'E'.$tmp['lon'].'N'.$tmp['lat'].'<HR>';
