<?php

/**
 * 市场部中秋活动
 * 
 * @package Better.Market
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Market_Midautumn extends Better_Market_Base
{
	
	public static function recUsers(array $params)
	{
		$results = array();
		$result = array(
			'count' => 0,
			'rows' => array(),
			'page' => 0,
			'pages' => 0,
			);

		$results = self::_recUids($params);
		if (count($results)>0) {
			$a = array_chunk($results, 30);
			$results = $a[0];
			$tmp = Better_DAO_User_Search::getInstance()->getUsersByUids($results, $params['page'] ? $params['page'] : 1, $params['count'], '', 'karma', 28);
			
			foreach ($tmp as $k=>$v) {
				$result[$k] = $v;
			}
			$result['count'] = count($results);
			$user = Better_Registry::get('user');

			$rows = $result['rows'];
			$result['rows'] = array();
			foreach ($rows as $key=>$value) {
				$value['message'] = Better_Blog::dynFilterMessage($value['message']);
				$value['status'] = $value['status'] ? unserialize($value['status']) : array();
				$value['location_tips'] = Better_User::filterLocation($value, 'blog');

				$result['rows'][] = $user->parseUser($value);
				$result['emails'][] = $value['email'];
			}
		}		
		
		return $result;		
	}
	
	protected static function _recUids(array $params)
	{
		$tmp = Better_DAO_Mafcard::getInstance()->getAll(array(
			'order' => 'dateline DESC'
			));
		foreach ($tmp as $row) {
			$results[] = $row['uid'];	
		}		

		return $results;
	}	
/*
 *         protected static function _recUids(array $params)
        {
                $results = array();
                $excludeUids = array(
                        $params['uid'],
                        );
                $cfgExcludeUids = (array)explode('|', Better_Config::getAppConfig()->reg_active_exclude);
                $eUids = array_merge($excludeUids, $cfgExcludeUids);

                $tmp = Better_DAO_User_Search::getInstance()->search('', $eUids, $params);

                foreach ($tmp as $row) {
                        $results[] = $row['uid'];
                }

                if (count($results)<$params['count']) {
                        $params['range'] = 10*$params['range'];
                        $results = self::_recUids($params);
                }

                return $results;
        }
 */	
}