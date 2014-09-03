<?php
/**
 * 文件读取更新poi基本信息
 * @var unknown_type
 */

define('BETTER_FORCE_INCLUDE', true);
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
define('BETTER_LOG_TIME', $appConfig->log_time ? true : false);
define('BETTER_HASH_POI_ID', $appConfig->hash_poi_id ? true : false);
define('BETTER_VER_CODE', '2010051108');
define('BETTER_NEED_INVITECODE', true);

if(file_exists(APPLICATION_PATH.'/../csv/batch4_flag.txt')){
	unlink(APPLICATION_PATH.'/../csv/batch4_flag.txt');
}

	$args = $_SERVER['argv'];
	$filename = $args[1] ? $args[1] : 'simi_high_'.date('Ymd',  strtotime("now")+8*3600).'.csv';
	$csv_path = $appConfig->simi_poi->csv_path;
	$file = $csv_path.$filename;
	
	if(file_exists($file)){
		$rows = Better_Csv2array::csvtoarray($file);
		
	try{
		file_put_contents(APPLICATION_PATH.'/../logs/Update_pois.log', $file.' '.date('Y-m-d H:i:s')."\n".'-------------------------------------'."\n", FILE_APPEND);
		foreach($rows as $row){
			if($row[0] && $row[2]){
				$new_address = array(
					'address'=>iconv('gb18030', 'utf-8', $row[2]),
				);
				
				$re = Better_DAO_Admin_Poi::getInstance()->update($new_address, $row[0]);
				Better_DAO_Poi_Fulltext::getInstance()->updateItem($row[0], 1);
				
				file_put_contents(APPLICATION_PATH.'/../logs/Update_pois.log', $row[0].'=>'.var_export($new_address, TRUE).$re."\n", FILE_APPEND);
				echo 'update poi: '.$row[0].'  '."$re"."\n";
			}
		}
		}catch(Exception $e){
			die($e);
		}
		echo $file.' Update '.count($rows)." rows successfully!\n";
		exit(0);
	}else{
		die('File: '.$file." can not be found!\n");
	}
	
