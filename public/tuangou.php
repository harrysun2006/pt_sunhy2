<?php
/**
 * 团购详细页面
 */
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

$sess = Better_Session::factory();
$sess->init();

header('Content-Type:text/html;charset=utf-8');

$tid = $_GET['id'] ? $_GET['id'] : 0;
$row = array();
if($tid){
	$row = Better_DAO_Roundmore_Tuangou::getInstance()->get($tid);
}

if(!$row['id']){
	die('无效的团购ID');
}
?>

<!doctype html>
<html>

<head>
	<title>团购详情</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; minimum-scale=1.0; user-scalable=false;"/>
	
	<style type="text/css">
		.clearfix{
			clear: both;
			height: 0;
		}
		body{
			background-color: #efefef;
			padding: 20px 18px;
		}
		.half_top{
			border: 2px solid #ababab;
			-moz-border-radius: 10px;
			-webkit-border-radius: 10px;
			border-radius: 10px;
			margin-bottom: 18px;
			background-color: #fff;
			padding: 20px;
		}
		.half_bottom{
			border: 2px solid #ababab;
			-moz-border-radius: 10px;
			-webkit-border-radius: 10px;
			border-radius: 10px;
			background-color: #fff;
		}
		h1, h2{
			margin:5px 0;
		}
		p{
			color: #666;
			font-size: 16px;
			font-weight: bold;
		}
		.price{
			background: url(/images/group2.png) no-repeat 50% 50%;
			height: 78px;
			padding: 10px 30px;
		}
		.w1{
			color: #000;
		}
	</style>
</head>

<body>
	<div class='half_top'>
		<div style='width: 40%; float: left;'>
			<img alt="" src="/images/group1.png">
		</div>
		<div style='width: 60%; float: left; text-align: right;'>
			<h1><?php echo $row['name'];?></h1>
			<h2 style='color: #666;'>来源：<img src='<?php echo $row['icon'];?>' style='vertical-align: middle;' width='30'/> <?php echo $row['source'];?></h2>
		</div>
		<div class='clearfix'></div>
		<div>
			<p>
				<?php echo $row['content'];?>
			</p>
		</div>
		<div class='price'>
			<div style='float: left; color: #fff; font-weight: bold; font-size: 30px; line-height: 80px; margin-left: 20px;'>
				￥&nbsp;<?php echo $row['price'];?>
			</div>
			<div style='float: right; line-height: 80px;'>
				<a href='<?php echo $row['detail_url'];?>'><img src='/images/group3.png' style='margin-top: 5px;'></a>
			</div>
			<div class='clearfix'></div>
		</div>
		<div style='font-size: 16px; color: #666; font-weight: bold; margin-top: 18px;'>
			<div style='margin: 0 auto; width: 500px; '>
				<span style='float: left;'>原价：<span class='w1'><?php echo '￥'.$row['value'];?></span></span>
				<span style='margin-left: 40px; float: left;'>折扣：<span class='w1'><?php echo $row['value'] ? round(($row['price']/$row['value'])*10, 1) : '0';?>折</span></span>
				<span style='margin-left: 40px; float: left;'>节省：<span class='w1'><?php echo '￥'.($row['value']-$row['price']);?></span></span>
				<div class='clearfix'></div>	
			</div>
		</div>
	</div>
	<div class='half_bottom'>
		<img src='<?php echo $row['img_url'];?>' width='100%'/>
	</div>
</body>

</html>

