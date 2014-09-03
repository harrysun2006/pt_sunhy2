<?php

/**
 * 每天的定期清理脚本
 * 
 * @package scripts
 * @author leip <leip@peptalk.cn>
 */

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

/*========================
 * 	清理过期的游戏通知
 *========================*/
$gameSess = Better_DAO_Game_Session::getInstance();
$select = $gameSess->getRdb()->select();
$select->from($gameSess->getTable());
$select->where('create_time>?', time()-24*3600);
$rs = Better_DAO_Game_Session::squery($select, $gameSess->getRdb());
$rows = $rs->fetchAll();
$sessIds = array();

foreach ($rows as $row) {
	$sessIds[] = $row['session_id'];	
}

if (count($sessIds)>0) {
	$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
	foreach ($sids as $sid) {
		
		$cs = Better_DAO_User_Assign::assignDbConnection('user_server_'.$sid);
		$wdb = $cs['w'];
		$deleteSql = $wdb->quoteInto("DELETE FROM `better_dmessage_receive` WHERE `sid` IN (?)", $sessIds);
		$wdb->query($deleteSql);
	}
}

Better_Log::getInstance()->logInfo('Notification cleaned', 'daily', true);

/*========================
 * 	清理过期游戏Session
 * =======================*/
$gameSess = Better_DAO_Game_Session::getInstance();
$gameWdb = $gameSess->getWdb();
$sql = "DELETE FROM `".$gameSess->getTable()."` WHERE `create_time`<'".(time()-24*3600)."'";
$gameSess->getWdb()->query($sql);

Better_Log::getInstance()->logInfo('Session cleaned', 'daily', true);

/*========================
 * 	清理过期Ping
 *=======================*/
$pingDao = Better_DAO_PingQueue::getInstance();
$sql = "DELETE FROM `".$pingDao->getTable()."` WHERE `queue_time`<'".(time()-24*3600)."'";
$pingDao->getWdb()->query($sql);

Better_Log::getInstance()->logInfo('Ping cleaned', 'daily', true);

/*=========================
 * 	清理过期的队列
 * 
 * 	清理过期的宝物箱
 * 
 * 	10天未登录的Karma减少
 * 
 * 	清理过期的游戏通知
 *========================*/
$oneWeek = 3600*24*7;
$deleteOffset = time()-$oneWeek;

$serverIds = Better_DAO_User_Assign::getInstance()->getServerIds();
foreach ($serverIds as $sid) {
	$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
	$wdb = &$cs['w'];
	$wdb->query("DELETE FROM `".BETTER_DB_TBL_PREFIX."email_queue` WHERE `queue_time`<".($deleteOffset));
	$wdb->query("DELETE FROM `".BETTER_DB_TBL_PREFIX."sync_queue` WHERE `queue_time`<".($deleteOffset));
	$wdb->query("DELETE FROM `".BETTER_DB_TBL_PREFIX."dmessage_receive` WHERE `type` LIKE 'game_%' AND dateline<".($deleteOffset));
	
	//	未激活帐号的过期宝物
	$rdb = &$cs['r'];
	$select = $rdb->select();
	$select->from(BETTER_DB_TBL_PREFIX.'profile AS p', array(
		'p.uid'
		));
	$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=p.uid', array(
		'c.treasures'
		));
	$select->where('c.treasures>?', 0);
	$select->where('p.state=?', Better_User_State::SIGNUP_VALIDATING);
	$rs = Better_DAO_Base::squery($select, $rdb);
	$rows = $rs->fetchAll();
	
	foreach ($rows as $row) {
		$deleted = $wdb->delete(BETTER_DB_TBL_PREFIX.'user_treasures', $wdb->quoteInto('uid=?', $row['uid']).' AND dateline<(UNIX_TIMESTAMP()-7*24*3600)');
		if ($deleted>0) {
			$treasures = ($row['treasures']-$deleted)>0 ? ($row['treasures']-$deleted) : 0;
			$wdb->query("UPDATE `".BETTER_DB_TBL_PREFIX."profile_counters` SET `treasures`='".intval($treasures)."' WHERE `uid`='".((int)$row['uid'])."'");
		}
	}
	
	Better_Log::getInstance()->logInfo('Validating treasures cleaned', 'daily', true);
	
	//	未手机验证的过期宝物
	$rdb = &$cs['r'];
	$select = $rdb->select();
	$select->from(BETTER_DB_TBL_PREFIX.'account AS a', array(
		'a.uid'
		));
	$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=a.uid', array(
		'c.treasures'
		));
	$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=a.uid', array());
	$select->where('c.treasures>?', 0);
	$select->where('a.cell_no IS NULL OR a.cell_no=?', '');
	$select->where('p.state!=?', Better_User_State::SIGNUP_VALIDATING);
	$rs = Better_DAO_Base::squery($select, $rdb);
	$rows = $rs->fetchAll();
	
	foreach ($rows as $row) {
		$deleted = $wdb->delete(BETTER_DB_TBL_PREFIX.'user_treasures', $wdb->quoteInto('uid=?', $row['uid']).' AND dateline<(UNIX_TIMESTAMP()-30*24*3600)');
		if ($deleted>0) {
			$treasures = ($row['treasures']-$deleted)>0 ? ($row['treasures']-$deleted) : 0;
			$wdb->query("UPDATE `".BETTER_DB_TBL_PREFIX."profile_counters` SET `treasures`='".intval($treasures)."' WHERE `uid`='".((int)$row['uid'])."'");
		}
	}
	
	Better_Log::getInstance()->logInfo('No cell treasures cleaned', 'daily', true);
	
	//	10天未登录
	$rdb = &$cs['r'];
	$select = $rdb->select();
	$select->from(BETTER_DB_TBL_PREFIX.'account AS a', array(
		'a.uid', 'a.lastlogin'
		));
	$select->where('a.lastlogin<?', time()-3600*240);
	$rs = Better_DAO_Base::squery($select, $rdb);
	$rows = $rs->fetchAll();
	$total = 0;
	
	foreach ($rows as $row) {
		$days = intval((time()-$row['lastlogin'])/(3600*24));
		$offset = fmod($days, 10);
		if ($offset==0) {
			$karmaToReduce = Better_User_Karma_Calculator::getInstance($row['uid'])->onNotLogin();
			Better_User_Karma::getInstance($row['uid'])->update(array(
				'karma' => $karmaToReduce,
				'category' => 'not_login',
				));
			$total++;
		}
	}
	
	echo "Total:".$total."\n";
}

/*========================
 * 	清理过期Lbs Cache
 *=======================*/
Better_DAO_Lbs_Cache::getInstance()->query("DELETE FROM `".BETTER_DB_TBL_PREFIX."lbs_cache` WHERE cache_time<".(time()-3600*48));

/*========================
 * 	重置爱帮忙的karma
 *=======================*/
$uid = (int)Better_Config::getAppConfig()->user->aibang_user_id;
if ($uid) {
	Better_DAO_User::getInstance($uid)->update(array(
		'karma' => 400
		));
}

/*========================
 * 	清理过期的通用Email队列
 *=======================*/
$commonDb = Better_DAO_Base::registerDbConnection('common_server');
$commonDb->query("DELETE FROM `".BETTER_DB_TBL_PREFIX."email_queue` WHERE `queue_time`<(UNIX_TIMESTAMP()-3600*24*7)");

/*========================
 * 	重置当天poi的计数
 *=======================*/

$poiDb = Better_DAO_Base::registerDbConnection('poi_server');
$rs = $poiDb->query("SELECT poi_id FROM ".BETTER_DB_TBL_PREFIX."poi WHERE last_update>UNIX_TIMESTAMP()-3600*24");
$rows = $rs->fetchAll();
foreach ($rows as $row) {
	$poiId = $row['poi_id'];
	$visitors = 0;
	$shouts = 0;
	$checkins = 0;
	$tips = 0;

	foreach ($sids as $sid) {
		$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
		$rdb = &$cs['r'];
		$wdb = &$cs['w'];

		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'user_place_log', array(
			new Zend_Db_Expr('COUNT(DISTINCT(uid)) AS total')
			));
		$select->where('poi_id=?', $poiId);

		$rs = Better_DAO_Base::squery($select, $rdb);
		$row = $rs->fetch();

		$visitors += (int)$row['total'];

		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
			new Zend_Db_Expr('COUNT(*) AS total')
 			));
		$select->where('poi_id=?', $poiId);
		$select->where('type=?', 'normal');
		$rs = Better_DAO_Base::squery($select, $rdb);
		$row = $rs->fetch();
		$shouts += (int)$row['total'];

		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('poi_id=?', $poiId);
		$rs = Better_DAO_Base::squery($select, $rdb);
		$row = $rs->fetch();
		$checkins += (int)$row['total'];

		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'blog', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('poi_id=?', $poiId);
		$select->where('type=?', 'tips');
		$row = $rs->fetch();
		$tips += (int)$row['total'];
	}

	$sql = "UPDATE `".BETTER_DB_TBL_PREFIX."poi` SET `tips`='".$tips."',`posts`='".$shouts."', `checkins`='".$checkins."', `users`='".$visitors."', `visitors`='".$visitors."' WHERE poi_id='".$poiId."'";
	$poiDb->query($sql);

	echo 'Poi '.$poiId.' Done.'."\n";
}

/*========================
 * 	重置当天poi的计数
 *=======================*/
$sql = "REPLACE INTO `".BETTER_DB_TBL_PREFIX."poi_major` SELECT poi_id, major, major_change_time, xy FROM `".BETTER_DB_TBL_PREFIX."poi` WHERE major_change_time>(".(time()-3600*24).") AND closed=0";
$poiDb->query($sql);
$sql = "DELETE FROM `".BETTER_DB_TBL_PREFIX."poi_major` WHERE poi_id IN (SELECT poi_id FROM `".BETTER_DB_TBL_PREFIX."poi` WHERE major_change_time>(".(time()-3600*24).") AND closed=1)";
$poiDb->query($sql);

/*========================
 * 	将用户自建poi转正
 *=======================
$sql = "UPDATE `".BETTER_DB_TBL_PREFIX."poi` SET `certified`=1 WHERE `creator`>0 AND `create_time`<(UNIX_TIMESTAMP()-3600*24)";
$poiDb->query($sql);
$sql = "REPLACE INTO `better_poi_index` 
	SELECT null, 1, `poi_id`, `city`, `category_id`, `name`, `xy`, `star`, `score`, `price`, `address`, `phone`, `label`, `link`, `logo`, `intro`, `country`, `province`, `creator`, `major`, `major_change_time`, `create_time`, `checkins`, `favorites`, `users`, `certified`, `visitors`, `posts`, `tips`, `aibang_id`, `ref_id`, `closed`, `last_update`, `ownerid`, `cid`, `region`, `forbid_major`, `level`
		FROM `better_poi` WHERE `creator`>0 AND `create_time`<(UNIX_TIMESTAMP()-3600*24)";
$poiDb->query($sql);*/

