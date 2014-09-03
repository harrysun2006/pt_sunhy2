<?php

/*
 * 是否强制使用php自己的include机制
 * 
 * apc在禁止了cache_by_default配置项后，可以利用Better_Loader与apc配合将绝大多数php文件cache在内存中
 * 用来减少每次php请求的io数
 * 
 * 但是该功能在某些情况下与Zend的某些类库似乎不那么兼容，虽然很少碰到，为了保险起见，外网服务器并没有激活该功能
 * 
 * 另：选用apc而不用xcache，最主要的原因是xcache不支持以concolse方式运行的php与apache模块方式运行的php之间共享cache
 */
define('BETTER_FORCE_INCLUDE', getenv('BETTER_FORCE_INCLUDE')!='' ? (bool)getenv('BETTER_FORCE_INCLUDE') : true);
define('BETTER_START_TIME', microtime());

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : ($_SERVER['APPLICATION_ENV'] ? $_SERVER['APPLICATION_ENV'] : 'production')));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require_once 'Better/Cache.php';
require_once 'Better/Cache/Handler/Base.php';
require_once 'Better/Loader.php';

/**
 * 文件自动装载器
 * 
 * 根据ZF的规范，通过一定格式的Class命名和目录结构，达到在引用某个Class时自动将需要的php文件包含进来
 * 该功能有一个可选常量
 */
Better_Loader::getInstance()->register();

/**
 * 加载项目配置
 */
Better_Config::load();
$appConfig = Better_Config::getAppConfig();

Better_Timer::start('prepare');

//	定义常量，减少array_key_exists调用
define('BETTER_NOW', time());
define('BETTER_BASE_URL', Better_Config::getAppConfig()->base_url);
define('BETTER_STATIC_URL', Better_Config::getAppConfig()->static_url);
define('BETTER_DB_TBL_PREFIX', Better_Config::getDbConfig()->global->tbl_prefix);											//	数据库表名前缀
define('BETTER_SYS_UID', $appConfig->user->sys_user_id);																					//	系统用户的uid
define('BETTER_VIRTUAL_UID', $appConfig->user->virtual_user_id);
define('BETTER_DB_DEBUG', Better_Config::getDbConfig()->global->debug==1 ? true : false);							//	是否打开调试模式（会记录某些特别的log)
define('BETTER_ENABLE_LOG', $appConfig->log->enable==1 ? true : false);
define('BETTER_QBS_DEBUG', $appConfig->qbs->debug==1 ? true : false);
define('BETTER_PAGE_SIZE', $appConfig->blog->page_size);																					//	默认的分页大宿
define('BETTER_MERGE_PAGE_SIZE', $appConfig->blog->merge_page_size);
define('BETTER_MAX_LIST_ITEMS', $appConfig->blog->list_max_items);
define('BETTER_MAX_LIST_ITEMS_START', $appConfig->blog->list_max_items_start);
define('BETTER_CACHE_HANDLER', $appConfig->cache->handler);
define('BETTER_LOG_TIME', $appConfig->log_time ? true : false);
define('BETTER_HASH_POI_ID', $appConfig->hash_poi_id ? true : false);
define('BETTER_AIBANG_POI', $appConfig->service->aibang->enabled ? true : false);
define('BETTER_VER_CODE', APPLICATION_ENV!='production' ? date('YmdHis') : '20110218000009');
define('BETTER_LOG_HASH', md5(uniqid(rand())));
define('BETTER_NEED_INVITECODE', $appConfig->in_testing ? true : false);
define('BETTER_PPNS_ENABLED', $appConfig->ppns->enabled ? true : false);
define('BETTER_8HOURS', 28800);
define('BETTER_ADMIN_DAYS', 680400);
define('BETETR_ACTIVITY_POI', $appConfig->activity_poi);
define('BETTER_HELP_UID', 126844);

Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
Better_Cache_BootStrap::getInstance()->startup();
Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);

$application = new Better_Application(APPLICATION_ENV, Better_Config::getFullConfig());
Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
$application->bootstrap()->run();
