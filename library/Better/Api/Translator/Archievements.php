<?php

/**
 * 个人成就
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Archievements extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];

		$result = array(
			'karma' => 0,
			'badges' => array(),
			);
		
		if ($data['karma']>0) {
			$result['karma'] = '+'.$data['karma'];
		} else if ($data['karma']<0) {
			$result['karma'] = '-'.abs($data['karma']);
		}
		
		if (is_array($data['badges']) && count($data['badges'])) {
			foreach ($data['badges'] as $badgeId) {
				$badge = Better_Badge::getBadge($badgeId)->getParams();
				$result['badges'][] = array(
					'badge' => Better_Api_Translator::getInstance('badge')->translate(array(
						'data' => &$badge,
						'userInfo' => $userInfo
						)),
					);
			}
		}
		
		return $result;
	}
}