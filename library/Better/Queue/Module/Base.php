<?php

/**
 * 
 * 队列处理基类
 * 
 * @package Better.Queue.Module
 * @author leip <leip@peptalk.cn>
 *
 */
abstract class Better_Queue_Module_Base
{
	protected $handler = null;
	
	protected function __construct()
	{
		$this->getHandler();
	}
	
	public function __destruct()
	{
		
	}
	
	/**
	 * 
	 * 获取后端队列处理对象
	 * 
	 * @return Better_Queue_Handler_Base
	 */
	protected function getHandler()
	{
		$handlerName = Better_Config::getAppConfig()->queue->handler;
		$handlerClass = 'Better_Queue_Handler_'.ucfirst(strtolower($handlerName));
		$this->handler = new $handlerClass;
	}
	
	/**
	 * 
	 * 将数据插入队列
	 * @param array $data
	 */
	abstract public function push(array $data);
	
	/**
	 * 
	 * 弹出队列数据
	 * @param unknown_type $step
	 */
	abstract public function pop(array $params);
	
	/**
	 * 
	 * 计算结果
	 * @param array $params
	 */
	abstract public function cal(array $params);
	
	/**
	 * 
	 * 标记队列处理完毕
	 * @param unknown_type $id
	 */
	abstract public function complete($id, $handleResult=1);
}