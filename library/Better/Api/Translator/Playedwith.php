<?php

/**
 * 与我玩过游戏的人数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Playedwith extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$playerInfo = &$params['data'];
		$userInfo = &$params['userInfo'];
		$dateline = $params['dateline'];

		$result = array(
			'last_time' => '',
			'user_concise' => array()
			);

		if (isset($playerInfo['uid'])) {
			$result['last_time'] = parent::time((float)$dateline);
			$result['user_concise'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
							'data' => &$playerInfo,
							'userInfo' => &$userInfo,
							));
		}
		
		return $result;
	}
}