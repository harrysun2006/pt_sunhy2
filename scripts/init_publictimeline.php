<?php
//	标定环境为Cron
define('IN_CRON', true);

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
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);

define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);

$sess = Better_Session::factory();
$sess->init();

$oneWeek = 3600*24*7;
$deleteOffset = time()-$oneWeek;

$args = $_SERVER['argv'];
$sid = (int)$args[1];
$from = (int)$args[2];
$offset = (int)$args[3];
$offset || $offset = 100;

$serverIds = Better_DAO_User_Assign::getInstance()->getServerIds();
$serverIds = array($sid);
$i = 1;
foreach ($serverIds as $sid) {
	echo "ServerID: ".$sid."\n";
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
	$wdb = &$cs['w'];
	$rdb = &$cs['r'];
	
	$sql = "SELECT uid,lastlogin FROM better_account ORDER BY lastlogin DESC LIMIT ".$from.", ".$offset;
	$rdb->getConnection();
	
	$rs = $rdb->query($sql);
	$rows = $rs->fetchAll();
	
	foreach ($rows as $row) {
		$uid = $row['uid'];
		echo ($i++)."\t Begin Uid: ".$uid.", LastLogin: ".date('Y-m-d H:i:s', $row['lastlogin'])."";
		Better_Timer::start('ipt_'.$uid);
		
		$sDao = Better_DAO_User_Status::getInstance($uid);
		$sDao->getRdb()->getConnection();
		$sDao->getWdb()->getConnection();
		
		$bids = $sDao->tinyWebFollowings(array(
			'page' => 1,
			'page_size' => 300,
			'with_self' => true,
			'without_kai' => true
			));
		$tmp = array_chunk($bids, 300);
		$bids = $tmp[0];
		echo " ".count($bids)." ";
		foreach ($bids as $row) {
			$bid = $row['bid'];
			$dateline = $row['dateline'];
			
			$pDao = Better_DAO_User_Publictimeline::getInstance($uid);
			$pDao->getRdb()->getConnection();
			$pDao->getWdb()->getConnection();
			
			$pDao->replace(array(
				'uid' => $uid,
				'bid' => $bid,
				'dateline' => $dateline
				));

		}
		echo ", Time used :".Better_Timer::end('ipt_'.$uid)."\n";
 //               sleep(3);
	}
}

echo "Done\n";

Better_Log::getInstance()->logInfo('cleaned', 'publictimeline_clean', true);
