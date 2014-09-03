<?php

/**
 * 签到后取附近好友签到
 * 
 * @package Better.Api.Translator
 * @author sunhy <sunhy@peptalk.cn>
 *
 */
class Better_Api_Translator_Friendblog extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$fblog = $params['fblog'];
		$before = $fblog['before'];
		$lang = Better_Language::get();
		if ($before >= 60) {
			$s1 = $lang->api->ac->fblog_before_minutes;
			$s1 = str_replace('{MINUTES}', (int)($before/60), $s1);
		} else {
			$s1 = $lang->api->ac->fblog_before_seconds;
			$s1 = str_replace('{SECONDS}', $before, $s1);
		}
		if ($params['poi_id'] == $fblog['poi_id']) { // 同一个POI
			$s2 = $lang->api->ac->fblog_poi_this;
		} else if ($fblog['distance'] == 0) { // 不同POI，距离为0
			$s2 = $lang->api->ac->fblog_poi_near;
			$s2 = str_replace('{POI_NAME}', $fblog['poi_name'], $s2);
		} else {
			$s2 = $lang->api->ac->fblog_poi_other;
			$s2 = str_replace('{POI_NAME}', $fblog['poi_name'], $s2);
			$s2 = str_replace('{DISTANCE}', $fblog['distance'], $s2);
		}
		$s = $lang->api->ac->fblog_message;
		$s = str_replace('{WHO}', $fblog['nickname'], $s);
		$s = str_replace('{WHEN}', $s1, $s);
		$s = str_replace('{WHERE}', $s2, $s);
		$s = str_replace('{WORD}', $fblog['message'], $s);
		return $s;
	}
}