<?php

/**
 * 大声展
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Getitlouder extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$config = Better_Config::getAppConfig();
		
		if ($config->getitloudermeeting->switch) {
			$now = time();
			$overtime = $config->poi->getitlouder->bj->overtime;
			$poiId = (int)$params['poi_id'];
			
			if ($now<=$overtime && $poiId==$config->poi->getitlouder->bj->id) {
				$result = true;	
			}
		}
		
		return $result;
	}
}