<?php

/**
 * 
 * “清理缓存”队列处理
 * 
 * @package Better.Queue.Module
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Queue_Module_Clearcache extends Better_Queue_Module_Base
{
	protected static $instance = null;
	
	protected function __construct()
	{
		parent::__construct();	
	}
	
	public function __destruct()
	{
		
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * 写入数据到队列
	 * 
	 * @see Better_Queue_Module_Base::push()
	 */
	public function push(array $data)
	{
		Better_DAO_Queue_Clearcache::getInstance()->insert(array(
			'queue_time' => time(),
			'clear_time' => 0,
			'module' => (int)$data['module'],
			'uid' => (int)$data['uid'],
			'result' => 0
			));
	}
	
	public function pop(array $params)
	{
		$row = array();
		
		$params['result'] = '0';
		$data = Better_DAO_Queue_Clearcache::getInstance()->get($params);
		if ($data['queue_id']) {
			$row = &$data;
		}
		
		return $row;
	}
	
	/**
	 * 队列处理方法
	 * 
	 * @see Better_Queue_Module_Base::cal()
	 */
	public function cal(array $params)
	{
		$result = false;

		switch ($params['module']) {
			//	用户相关blog缓存
			case '0':
				$result = $this->calUserBlog($params);
				break;
			default:
				$result = true;
				break;
		}
		
		return $result;
	}
	
	protected function calUserBlog($params)
	{
		$uid = (int)($params['uid']);
		$result = false;
		
		if ($uid) {
			Better_Cache_Clear::userAvatar($uid);
			Better_Cache_Clear::blog($uid);
		}
		
		return $result;
	}
	
	public function complete($id, $handleResult=1)
	{
		$result = Better_DAO_Queue_Clearcache::getInstance()->reconnection()->updateByCond(array(
			'clear_time' => time(),
			'result' => 1
			), array(
				'queue_id' => $id
			));
			
		return $result;
	}
}