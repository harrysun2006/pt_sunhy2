<?php

/**
 * 调试用的日志记录
 * 这个Class生成的所有日志都在开开项目的logs目录下
 * 所有文件名都是 *.log 的格式
 * 所有文件内容都参考了apache标准格式，然后在尾部做了自己的扩展（具体格式参见 _log 方法）
 *
 * @package Better
 * @author leip <lei@peptalk.cn>
 *
 */
class Better_Log
{
	protected static $instance = null;
	protected $logSavePath = '';
	protected $params = array();
	protected $data = array();
	protected $prepare = array();
	
	protected function __construct()
	{
		$this->logSavePath = APPLICATION_PATH.'/../logs';
	}
	
	/**
	 * 析构器
	 * 将日志记录的操作放到析构器里面去执行，增加页面加载速度
	 * 
	 * @return unknown_type
	 */
	public function __destruct()
	{
		try {
			foreach ($this->params as $param) {
				$this->_log($param['msg'], $param['file'], $param['level']);
			}
			
			$module = defined('BETTER_CONTROLLER_MODULE') ? BETTER_CONTROLLER_MODULE : 'scripts';
			$this->_log($module, 'trace', 'info');
			
			if (count($this->data)>0) {
				
			}
			
		} catch (Exception $e) {
			error_log($e->getTraceAsString());
		}
	}
	
	public static function getInstance()
	{
		if (!(self::$instance instanceof Better_Log)) {
			self::$instance = new Better_Log();
		}
		
		return self::$instance;
	}
	
	public function __call($method, $params)
	{
		if (preg_match('/^log([a-zA-Z]+)$/is', $method)) {
			$level = preg_replace('/^findAll([a-zA-Z0-9_]+)/is', '\1', $method);
			$now = isset($params[2]) ? (bool)$params[2] : false;
			$this->log($params[0], $params[1], preg_replace('/^log([a-zA-Z]+)$/is', '\1', strtolower($level)), $now);
		}
	}
	
	/**
	 * 
	 * 预先放置log内容到数组
	 * @param unknown_type $str
	 */
	public function prepare($str)
	{
		$this->prepare[] = "[".Better_Timer::end('prepare', true)."]\t".$str;
		if (APPLICATION_ENV=='home_linux') {
			$this->prepareDone();
			$this->prepare = array();
		}
	}
	
	/**
	 * 
	 * 将预先准备的log写入文件
	 */
	public function prepareDone()
	{
		$this->log("\n".implode("\n", $this->prepare)."\n", 'prepare', true);
	}
	
	/**
	 * 
	 * 将待记录的内容载入数组
	 * 
	 * @param unknown_type $data
	 * @param unknown_type $filename
	 */
	public function putData(array $data, $filename='global')
	{
		if (count($data)>0) {
			foreach ($data as $key=>$value) {
				$this->data[$filename][$key] = $value;
			}
		}
	}
	
	/**
	 * 
	 * 将记录的内容写入日志，并清除数组
	 * 
	 * @param unknown_type $filename
	 */
	public function writeData($filename='global')
	{
		$log = "\n";
		
		$log .= serialize($this->data[$filename]);
		
		$log .= "\n";
		
		$this->log($log, $filename);
	}
	
	/**
	 * 记录程序运行时间
	 * 
	 * @param unknown_type $what
	 */
	public function logTime($what)
	{
		if (BETTER_LOG_TIME) {
			$this->logInfo($what.':['.Better_Functions::execTime().'], DB Queries:['.Better_DAO_Base::getQueries().']', 'timer', true);
		}
	}
	
	/**
	 * 记录要保存的日志的参数
	 * 
	 * @param $msg	日志的内容
	 * @param $file		日志的文件名
	 * @param $level	日志的警告级别
	 * @param $now 是否立即写入文件系统（否则将在php请求结束时才写入，对于后端运行的php，这里应该设置为true）
	 * @return unknown_type
	 */
	public function log($msg, $file='general', $level='info', $now=true)
	{
		$this->_log($msg, $file, $level);
	}
	
	/**
	 * 记录一个日志
	 *
	 * @param string $msg		日志的内容
	 * @param string $file		日志文件（根据不同的类别分别保存）
	 * @param string $level	日志的消息级别
	 * @return null
	 */
	protected function _log($msg, $file='general', $level='info')
	{

		if (defined('BETTER_ENABLE_LOG') && BETTER_ENABLE_LOG==true) {			
			$logFile = $this->logSavePath.'/'.$file.'.log';
			
			$uid = 0;
			$fromId = 0;
			$partner = '';
			$email = '';
			$clientLanguage = $client = $ver = $model = '';
			
			try {
				if (is_object(Better_Registry::get('sess'))) {
					$uid = intval(Better_Registry::get('sess')->getUid());
					$fromId = Better_Registry::get('sess')->get('web_from');
					
					if ($uid) {
						$user = Better_User::getInstance($uid);
						$userInfo = $user->getUserInfo();
						$email = $userInfo['email'];
					}
				}
			} catch(Exception $e) {
				$uid = 0;
			}
			
			if (isset($_GET['kai_partner'])) {
				$partner = $_GET['kai_partner'];
			} else if (isset($_POST['kai_partner'])) {
				$partner = $_POST['kai_partner'];
			}
			
			if (defined('IN_CRON')) {
				$cond = 'cron';
			} else if (defined('IN_CONSOLE')) {
				$cond = 'console';
			} else if (defined('IN_API')) {
				$cond = 'api';
				
				if ($uid) {
					$clientCache = Better_User::getInstance($uid)->cache()->get('client');
					
					$client = $clientCache['platform'];
					$ver = $clientCache['ver'];
					$model = $clientCache['model'];
					$clientLanguage = $clientCache['language'];
				}
			} else {
				$cond = 'web';
			}

			$ip = Better_Functions::getIP();
			if (APPLICATION_ENV!='production') {
				$log = $cond.' '.APPLICATION_ENV;
			} else {
				$log = $cond;
			}
			
			$log .= ' '.$ip.' ['.$_SERVER['REQUEST_METHOD'].']['.intval($uid).'] ['.$fromId.'] ['.$partner.'] '.$_SERVER['REQUEST_URI'].' '.'POST:['.serialize($_POST).'], GET:['.serialize($_GET).']'.' '.$msg;
			$log = date('Y-m-dTH:i:s').' '.$log."\n";

			$partner = $partner ? $partner : '-';
			$email = $email ? $email : '-';
			$client = $client ? $client : '-';
			$model = $model ? $model : '-';
			$clientLanguage = $clientLanguage ? $clientLanguage : '-';
			$ver = $ver ? $ver : '-';
			$fromId = $fromId ? $fromId : '-';
			
			$runTime = intval(Better_Application::runTime()*1000000);
			
			$requestLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '-';
			$baseAuthUser = $_SERVER['PHP_AUTH_USER'] ? $_SERVER['PHP_AUTH_USER'] : '-';
			$baseAuthPwd = $_SERVER['PHP_AUTH_PW'] ? $_SERVER['PHP_AUTH_PW'] : '-';
			
			$model = str_replace(' ', '', $model);
			$ver = str_replace(' ', '', $ver);
			$client = str_replace(' ', '', $client);
			
			$gateway = Better_Functions::gateway();
			$gateway = $gateway ? $gateway : '-';
			
			$memoryUsage = memory_get_usage();
			$unit=array('b','kb','mb','gb','tb','pb');
			$memoryUsage = @round($memoryUsage/pow(1024,($i=floor(log( abs($memoryUsage==0?1:$memoryUsage) ,1024)))),2).$unit[$i];
			$fulltext = 'f:' . Better_Registry::get('FULLTEXT');
			$lbs = 'l:' . Better_Registry::get('LBS');
			
			$_squidCode = Better_Registry::get('squid_code');
			if (!$_squidCode) $_squidCode = 0; 
			$logs = array(
				$ip,																																												//	IP
				'-',																												
				$email,																																											//	用户Email
				'['.date('d/M/Y:H:i:s O').']',	//	[20/Jun/2010:23:59:49 +8000]																					//	请求时间
				'"'.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' HTTP/1.0"',														//	请求方法及地址
				'200',																																											//	Response Code
				'0',																																												//	Content-Length
				'['.$runTime.']',																																							//	执行时间
				$uid,																																											//	用户uid
				'['.serialize($_POST).'][' . $_squidCode .'][kai]',																																		//	Post变量
				$fromId ,																																										//	FromId
				$partner,																																										//	Partner
				$client,																																											//	客户端类型
				$ver,																																											//	客户端版本
				$model,																																										//	客户端Model
				$clientLanguage,																																							//	客户端语言
				$requestLanguage,																																						//	本次请求语言
				$baseAuthUser,																																							//	Basic认证的用户名
				$baseAuthPwd,																																							//	Basic认证的密码
				$gateway,																																									//	网关
				$memoryUsage,
				$fulltext,
				$lbs,																																						//	内存用量
				//	===============	待定	====================
				$msg																																											//	日志消息
				);
			$log = implode(' ', $logs)."\n";
			//日志在凌晨的时候会移动，导致日志不能写入
			try{
				error_log($log, 3, $logFile);
				if ($runTime > 5000000 && $file == 'trace' && count($_FILES) == 0 && $msg != 'scripts') {
					$logFile = $this->logSavePath.'/' . $file . '5' . '.log';
					error_log($log, 3, $logFile);
				}
			} catch(Exception $e){
				
			}
		}
		
	}
}