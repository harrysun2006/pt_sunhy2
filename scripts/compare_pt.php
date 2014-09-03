<?php

/**
 * 新的publictimeline开发过程中的一个对比脚本，用来对比新、旧两种方式取出的数据是否一致
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

//	标定环境为Cron
define('IN_CRON', true);

define('BETTER_FORCE_INCLUDE', getenv('BETTER_FORCE_INCLUDE')!='' ? (bool)getenv('BETTER_FORCE_INCLUDE') : true);

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

$args = $_SERVER['argv'];
$uid = $args[1];
$params = array(
	'page' => 1,
	'page_size' => 60,
	'without_kai' => true
	);
$from = (int)$args[2];
$to = (int)$args[3];

if ($uid=='part0' || $uid=='part1') {
	if ($uid=='part0') {
		$sid = '1';
	} else {
		$sid = '2';
	}
	
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
	$rdb = &$cs['r'];
	
	$sql = "SELECT uid,FROM_UNIXTIME(MAX(dateline)) FROM `better_blog` 
WHERE 1 
GROUP BY uid
ORDER BY dateline desc
";
	
	if ($to) {
		$sql .= " LIMIT ".$from.", ".$to;
	}
	$rs = $rdb->query($sql);
	$rows = $rs->fetchAll();
	$diffUids = array();
	$i = 1;
	
	foreach ($rows as $row) {
		$uid = $row['uid'];
		echo ($i++)."\tCompare uid ".$uid." ...\t\t";
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		$new = Better_DAO_User_Publictimeline::getInstance($uid)->getMine($params);
		$newBids = array();
		if ($new) {
			foreach ($new as $bid) {
				$newBids[] = $bid;
			}
		}
		
		if (count($newBids)<$params['page_size'] && count($newBids)>0) {
			$params['page_size'] = count($newBids);
		}
				
		$params['without_kai'] = false;
		$old = Better_DAO_User_Status::getInstance($uid)->webFollowings($params);
		$data = array_chunk($old, $params['page_size']);
		$old = $data[$params['page']-1];
		
		$oldBids = array();
		foreach ($old as $row) {
			$oldBids[] = $row['bid'];
		}

		$newBids = array_unique($newBids);
		$oldBids = array_unique($oldBids);
		
		$diff = array_diff($newBids, $oldBids);
		if (count($diff)>0) {
			$diffUids[] = $uid;
			echo "NOT SAME : new:(".count($newBids)."), old:(".count($old).")\n";
		} else {
			echo "OK\n";
		}
	}
	
	echo "\n\nThere ".count($diffUids)." users' lists are diferent:\n";
	var_dump($diffUids);
	echo "\nThere ".count($diffUids)." users' lists are diferent\n\n";
} else if (is_numeric($uid) && $uid) {
	$uid = (int)$uid;
	echo "\n Compare two ways to get publictimeline ....\n";
	$user = Better_User::getInstance($uid);
	$userInfo = $user->getUserInfo();
	echo "\nUsername is ".$userInfo['username'].", nickname is ".$userInfo['nickname']."\n";
	
	$new = Better_DAO_User_Publictimeline::getInstance($uid)->getMine($params);
	$newBids = array();
	foreach ($new as $bid) {
		$newBids[] = $bid;
	}
	
	if (count($newBids)<$params['page_size'] && count($newBids)>0) {
		$params['page_size'] = count($newBids);
	}
		
	$params['without_kai'] = false;
	$old = Better_DAO_User_Status::getInstance($uid)->webFollowings($params);
	$data = array_chunk($old, $params['page_size']);
	$old = $data[$params['page']-1];
	
	$oldBids = array();
	foreach ($old as $row) {
		$oldBids[] = $row['bid'];
	}

	$newBids = array_unique($newBids);
	$oldBids = array_unique($oldBids);
			
	echo "\nCount of New is ".count($newBids)."\n";
	echo "Count of Old is ".count($oldBids)."\n";
	$diff = array_diff($newBids, $oldBids);
	echo "\n\n";
	echo "Rows In Two results\n";
	echo "|\tRow\t|\tOld \t|\tNew \t |\n";
	for ($i=0;$i<count($oldBids);$i++) {
		$isDiff = $oldBids[$i]==$newBids[$i] ? "" : " ---> Is Diff";
		echo "|\t".$i."\t| ".$oldBids[$i]." | ".$newBids[$i]." |".$isDiff."\n";
	}
	echo "\n\n";
	echo "\nUsername is ".$userInfo['username'].", nickname is ".$userInfo['nickname']."\n";
}

echo "Done\n";