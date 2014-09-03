<?php
/**
 * 编译js、css输出
 * 
 */

define('BETTER_FORCE_INCLUDE', getenv('BETTER_FORCE_INCLUDE') ? getenv('BETTER_FORCE_INCLUDE') : true);
define('BETTER_START_TIME', microtime());

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : ($_SERVER['APPLICATION_ENV'] ? $_SERVER['APPLICATION_ENV'] : 'production')));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

$mode = $_GET['mode'] ? $_GET['mode'] : 'js';
$tmp = $_GET['files'];
$mode!='css' && $mode = 'js';

$files = explode(',', $tmp);
$content = '';
$path = (APPLICATION_ENV=='production' || APPLICATION_ENV=='testing_main2' || APPLICATION_ENV=='testing') ? $mode.'_combined' : $mode;
$path = dirname(__FILE__).'/'.$path;

foreach ($files as $file) {
	list($c, $f) = explode(':', $file);
	if ($f) {
		if (preg_match('/^([a-zA-Z0-9]{3,20})$/i', $c) && preg_match('/^([0-9a-zA-Z]{3,20})$/i', $f)) { 
			$toRead = $path.'/'.$c.'/'.$f.'.'.$mode;
			$content .= file_get_contents($toRead)."\n\n";
		}
	} else {
		if (preg_match('/^([0-9a-zA-Z]{3,20})$/i', $c)) {
			$toRead = $path.'/'.$c.'.'.$mode;
			$content .= file_get_contents($toRead)."\n\n";
		}
	}
}

@ob_start('gz_handler');
if ($mode=='css') {
	header('Content-Type: text/css; charset=utf-8');
} else {
	header('Content-Type: text/javascript; charset=utf-8');
}
echo $content;