<?php

/**
 * 短信功能报警
 * 
 * @package Better.Monitor
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Monitor_Sms
{
	
	public static function send($mobile, $content)
	{
		$config = Better_Config::getAppConfig();
		if ($config->sms->monitor) {
			$flag = Better_DAO_Sms_Monitor::getInstance()->insert(array(
				$mobile,
				$content
				));
                        if (!$flag) {
                          Better_Log::getInstance()->logInfo('Insert_Queue_Failed', 'monitor');
                        }
		}
	}
}
