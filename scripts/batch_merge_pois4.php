<?php
/**
 * 文件读取自动合并  带更新poi基本信息功能的
 * row[0]  update_flag 表示是否更新
 * row[1]  被合掉的poi_id  || 需更新的poi_id
 * row[2]  合并进的poi_id，即目标poi || 需更新的poi_id
 * row[3]  更新的层级
 * row[4]  更新的poi名称
 * row[5]  更新的poi地址
 * 
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
		
		$last_key = 0;
		$first = 0;
		if(file_exists(APPLICATION_PATH.'/../csv/last_key4.txt')){
			$last_key = file_get_contents(APPLICATION_PATH.'/../csv/last_key4.txt');
		}else{
			$first = 1;
		}
		
		$result = array();
		$update_result = array();
		
		$start = 0;
		foreach($rows as $row){
			if($row[0]==1 && $row[1]){
				$update_result[$row[1]] = array(
					'level'=> $row[3], 
					'level_adjust'=> $row[3], 
					'name'=>iconv('gb18030', 'utf-8', $row[4]), 
					'address'=>iconv('gb18030', 'utf-8', $row[5]),
					'certified'=>1
				);
			}else{
				if($row[1] && $row[2]){
					if($first==0 &&  $row[2]==$last_key){
						$start = 1;
					}
					if($start == 1 || $first==1){
						$result[$row[2]][] = $row[1];
					}
				}
			}
		}
		
		try{
		
		$poisupdate = array();
			
		file_put_contents(APPLICATION_PATH.'/../logs/Merge_pois.log', $file.' '.date('Y-m-d H:i:s')."\n".'-------------------------------------'."\n", FILE_APPEND);
		foreach($result as $k=>$val){
			file_put_contents(APPLICATION_PATH.'/../csv/last_key4.txt', $k);
			$pids = array_unique($val);
			$refParams = array(
				'pids'=> $pids,
				'target_poi_id' => $k
			);	
			
			$simipoi = new Better_Admin_Simipoi();
			$return = $simipoi->mergeMutiPOI($pids, $k);
			
			$poisupdate[] = $k;
			
			file_put_contents(APPLICATION_PATH.'/../logs/Merge_pois.log', var_export($refParams, TRUE)."\n".$return."\n", FILE_APPEND);
			echo $return."  ".$k."\n";
		}
		
		file_put_contents(APPLICATION_PATH.'/../logs/Update_pois.log', $file.' '.date('Y-m-d H:i:s')."\n".'-------------------------------------'."\n", FILE_APPEND);	
		foreach($update_result as $uk=>$uval){
			$re = Better_DAO_Admin_Poi::getInstance()->update($uval, $uk);
			Better_DAO_Poi_Fulltext::getInstance()->updateItem($uk, 1);
			$poisupdate[] = $uk;
			
			file_put_contents(APPLICATION_PATH.'/../logs/Update_pois.log', $uk.'=>'.var_export($uval, TRUE).$re."\n", FILE_APPEND);
			print 'update poi: '.$uk.'  '."$re"."\n";
		}
		
		
		/*$poisupdate = array_unique($poisupdate);
		file_put_contents(APPLICATION_PATH.'/../logs/Pois_update.log', $file.' '.date('Y-m-d H:i:s')."\n".'------------------------'.var_export($poisupdate, TRUE).count($poisupdate)."\n", FILE_APPEND);
		
		foreach($poisupdate as $p){
			//进入poi更新库
			if($p){
				$flag = false;
				$kpoi = Better_Poi_Info::getInstance($p)->getBasic();
				Better_Poi_Info::destroyInstance($p);
				
				if($kpoi['closed']){
					while($kpoi['ref_id'] && $kpoi['ref_id']!=$p){
						$kpoi = Better_Poi_Info::getInstance($kpoi['ref_id'])->getBasic();
						Better_Poi_Info::destroyInstance($kpoi['ref_id']);
							
						if(!$kpoi['closed']){
							$flag = true;
							break;
						}
					}
					
				}else{
					$flag = true;
				}
				
				if($flag){
					Better_DAO_Admin_Poiupdate::getInstance()->insert(array(
						'poi_id'=>$kpoi['poi_id'],
						'dateline'=>time(),
						'name'=>$kpoi['name']
					));	
				}
				
			}
		}*/
		
		}catch(Exception $e){
			die($e);
		}
		unlink(APPLICATION_PATH.'/../csv/last_key4.txt');
		file_put_contents(APPLICATION_PATH.'/../csv/batch4_flag.txt', 'done');
		echo $file.' Merge '.count($result)." rows successfully!\n";
		echo $file.' Update '.count($update_result)." rows successfully!\n";
		
		exit(0);
	}else{
		die('File: '.$file." can not be found!\n");
	}
	
