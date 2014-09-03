<?php

define('PID_FILE', dirname(__FILE__).'/publictimeline_kai.pid');

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

$uid = BETTER_SYS_UID;
$cacher = Better_Cache::remote();

while(true) {
	try {
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$sql = "SELECT bid, dateline
		FROM `".BETTER_DB_TBL_PREFIX."blog`
		WHERE uid='".$uid."' AND priv!='private'
		ORDER BY dateline DESC
		LIMIT 300
		";
		$rows = Better_DAO_Base::squery($sql, $rdb);
		$data = array();
		
		$max = 0;
		$min = 0;
		foreach ($rows as $row) {
			$dateline = $row['dateline'];
			$bid = $row['bid'];
			
			$data[$dateline.'.'.$bid] = $bid;
			
			!$min && $min = $dateline;
			!$max && $max = $dateline;
			
			$dateline>$max && $max = $dateline;
			$dateline<$min && $min = $dateline;
			
		}
		
		$cachedMin = (int)$cacher->get('kai_pt_min');
		$cachedMax = (int)$cacher->get('kai_pt_max');

		if ($max!=$cachedMax || $min!=$cachedMin) {
			echo "Cache Changed.\n\n";
			$cacher->set('kai_pt_min', $min);
			$cacher->set('kai_pt_max', $max);

			$cacher->set('kai_pt', $data);
		}
		
		sleep(60);
		touch(PID_FILE);
	} catch (Exception $e) {
		$log = $e->getMessage()."\n".$e->getTraceAsString();
		Better_Log::getInstance()->logInfo($log, 'publictimeline_kai_crash', true);
		exit(0);
	}
}
