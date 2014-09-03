<?php
/**
 * 开启关闭poi
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

	$args = $_SERVER['argv'];
	$filename = $args[1] ? $args[1] : '';
	$csv_path = $appConfig->simi_poi->csv_path;
	$file = $csv_path.$filename;
	try{
	if(file_exists($file)){
		$rows = Better_Csv2array::csvtoarray($file);
		foreach($rows as $row){
			if(is_array($row)){
				$flag = false;
				unset($row[count($row)-1]);
				if(count($row)>0){
					foreach ($row as $val){
						$poi = Better_Poi_Info::getInstance($val)->getBasic();
						if($poi['poi_id'] && !$poi['closed']){
							$flag = true;
							break;
						}
					}
				
					if($flag){
						file_put_contents(APPLICATION_PATH.'/../logs/fix_closed_pois.log', "open\n", FILE_APPEND);
					}else{
						file_put_contents(APPLICATION_PATH.'/../logs/fix_closed_pois.log', "closed\n", FILE_APPEND);
					}
				
					if(!$flag){
						$count = 0;
						$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
						foreach($row as $v){
							$flag1 = false;
							foreach($sids as $sid){
								$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
								$rdb = $cs['r'];
								$rs = $rdb->query('select count(bid) as count from better_blog where poi_id='.$v);
								$result = $rs->fetch();
								if($result && $result['count']>0){
									$flag1 = true;
									break;
								}
							}
							if($flag1){
								Better_DAO_Admin_Poi::getInstance()->update(array('ref_id'=>'', 'closed'=>0), $v);
								Better_DAO_Poi_Fulltext::getInstance()->updateItem($v, 1);
								Better_Poi_Info::destroyInstance($v);
								
								file_put_contents(APPLICATION_PATH.'/../logs/fix_closed_pois.log', '----open poi:'.$v."\n", FILE_APPEND);
								
								break;
							}else{
								$count++;
							}
						}
						
						if($count!=0 && $count==count($row)){
							Better_DAO_Admin_Poi::getInstance()->update(array('ref_id'=>'', 'closed'=>0), $row[0]);
							Better_DAO_Poi_Fulltext::getInstance()->updateItem($row[0], 1);
							Better_Poi_Info::destroyInstance($row[0]);
							
							file_put_contents(APPLICATION_PATH.'/../logs/fix_closed_pois.log', '------all pois in this row no vistors-->open poi:'.$row[0]."\n", FILE_APPEND);
						}
					}
				}
			}
		}	
	}else{
		die('File: '.$file." can not be found!\n");
	}
	}catch(Exception $e){
		die($e);
	}
	
