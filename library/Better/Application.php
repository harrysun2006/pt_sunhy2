<?php

/**
 * 继承自Zend的Better应用程序
 * 其实到目前位置就是为了在某次php请求结束以后写入一些log
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Application extends Zend_Application
{

	public function __construct($environment, $options = null)
	{
		parent::__construct($environment, $options);
	}

	public function __destruct()
	{
		try {
			$pageExecTime = round(self::runTime(), 6);
			
			$dbQueries = Better_DAO_Base::getQueries();
			
			Better_Log::getInstance()->logInfo('Files: ['.count(get_included_files()).'], Time: ['.$pageExecTime.'], Queries: ['.$dbQueries.'], QueryTime:['.Better_DAO_Base::$queryTime.']', 'page_exec', true);
			if ($pageExecTime>1) {
				Better_Log::getInstance()->prepareDone();
			}
		} catch (Exception $e) {

		}
	}
	
	public static function runTime()
	{
		$mtime = explode(' ', microtime());
		$end = $mtime[1]+$mtime[0];
		$mtime = explode(' ', BETTER_START_TIME);
		$start = $mtime[1]+$mtime[0];
		
		$pageExecTime = $end-$start;

		return $pageExecTime;
	}
}