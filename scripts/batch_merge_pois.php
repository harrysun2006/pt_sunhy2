<?php
/**
 * 老版本的csv格式，暂时用不到
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
	$filename = $args[1] ? $args[1] : 'simi_high_'.date('Ymd',  strtotime("now")+8*3600).'.csv';
	$csv_path = $appConfig->simi_poi->csv_path;
	$file = $csv_path.$filename;
	
	if(!$args[1] && !file_exists($file)){
		exec($csv_path.'get_simi_high.sh');
	}
	
	if(file_exists($file)){
		$rows = Better_Csv2array::csvtoarray($file);
		
		$tmp = $result = array();
		foreach($rows as $row){
			$tmp[$row[0]][] = $row;
		}
		
		foreach($tmp as $k=>$pois){
			if($k){
				$max = 0;
				foreach($pois as $poi){//poi[0]=>item_id  poi[1]=>poi_id poi[2]=>flag poi[3]=>name poi[4]=>address
					if($poi[1]){
						if($poi[2]>$max){
							$max = $poi[2];
							$result[$k]['target_poi_id'] = $poi[1];
						}
						
						$result[$k]['pids'][$poi[1]] = $poi[1];
					}
				}
				
				if($max==0){//全部为0
					$result[$k]['target_poi_id'] = $pois[0][1];
				}
				unset($result[$k]['pids'][$result[$k]['target_poi_id']]);
			}
		}
 		 
		//$result = array(33=>array('pids'=>array(89511, 91873), 'target_poi_id'=>90808));
		//Zend_Debug::dump($result);exit();
		try{
		file_put_contents(APPLICATION_PATH.'/../logs/Merge_pois.log', $file.' '.date('Y-m-d H:i:s')."\n".'-------------------------------------'."\n", FILE_APPEND);
		foreach($result as $val){
			$refParams = array(
				'pids'=> $val['pids'],
				'target_poi_id' => $val['target_poi_id']
			);	
			
			Better_Admin_Poi::refMutiPOI($refParams);
			$return = Better_Admin_Simipoi::mergeMutiPOI($val['pids'], $val['target_poi_id']);
			
			file_put_contents(APPLICATION_PATH.'/../logs/Merge_pois.log', var_export($refParams, TRUE)."\n".$return."\n", FILE_APPEND);
			echo $return."\n";
		}
		}catch(Exception $e){
			die($e);
		}
		
		echo $file.' Process '.count($result)." rows successfully!\n";
		exit(0);
	}else{
		die('File: '.$file." can not be found!\n");
	}
	
