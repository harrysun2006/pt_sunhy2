<?php

/**
 * 
 * 队列处理
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Queue
{
	
	/**
	 * 
	 * 获取一个处理器对象
	 * 
	 * @param unknown_type $handler
	 */
	public static function getHandler()
	{
		
	}
	
	public static function getQueue($queue)
	{
		
	}
	
	public static function push(array $queues, array $data=array())
	{
		if (count($queues)>0) {
			foreach ($queues as $queue) {
				try {
					$className = 'Better_Queue_Module_'.ucfirst(strtolower($queue));
					$queueObj = call_user_func($className.'::getInstance');
					$queueObj->push($data);
				} catch (Exception $e) {
					Better_Log::getInstance()->logInfo($e->getMessage().":\n".$e->getTraceAsString(), 'queue', true);
					continue;
				}
			}
		}
	}
}