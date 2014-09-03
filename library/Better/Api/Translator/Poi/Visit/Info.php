<?php

/**
 * POI访问数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Poi_Visit_Info extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();

		$data = &$params['data'];
		if (isset($data['poi_id'])) {
			$lang = Better_Language::get();
			
			//这是你今天第1站！
			$p1 = $lang->api->ac->my_today_visits;
			$p1 = str_replace('{TOTAL}', $data['my_today_visits'], $p1);
			
			$result[] = array(
				'message' => $p1
				);
			
			//你曾经来过2次，今天第1次来。
			$p2 = $lang->api->ac->my_poi_summary;
			$p2 = str_replace('{TODAY}', $data['my_today_poi_visits'], $p2);
			$p2 = str_replace('{VISIT}', $data['my_poi_visits'], $p2);
			
			$result[] = array(
				'message' => $p2
				);
//				
//			//去访客里看看，今天有2个人已经来过这了
//			$p3 = $lang->api->ac->poi_visit_summary;
//			$p3 = str_replace('{TODAY}', $data['poi_today_visits'], $p3);
//			$result[] = array(
//				'message' => $p3
//				);
			if ($data['this_score'] == 0) {
				$result[] = array('message' => $lang->api->ac->noaward);
			}
		}	
			
		return $result;
	}
}