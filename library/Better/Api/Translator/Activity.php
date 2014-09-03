<?php

/**
 * 市场活动
 * 2011-4-2 修改 by guoym <guoym@peptalk.cn>
 * 活动终于独立了！
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Activity extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];

		$result = array();
		
		if (isset($data['act_id'])) {
			$result['id'] = $data['act_id'];
			$result['title'] = $data['title'];
			$result['content'] = $data['content'];
			$result['image_url'] = $data['image_url'];
			$result['attach_url'] = $data['attach_url'];
			$result['closed'] = $data['checked'] != 1 ? 'true' : 'false';
			$result['start_time'] = parent::time($data['begintm']);
			$result['end_time'] = parent::time($data['endtm']);
			$result['action'] = $this->_action($data);
/*			$result['poi_count'] = $data['poi_count'];
			$result['pois'] = array();
			if (is_array($data['pois'])) {
				foreach ($data['pois'] as $poi) {
					$result['pois'][] = array(
						'poi_concise' => Better_Api_Translator::getInstance('poi_concise')->translate(array(
							'data' => &$poi,
						))
					);
				}
			}
*/		}
		
		return $result;
	}
	
	private function _action($data)
	{
		if(isset($data['sms_no']) && $data['sms_no']){
			$result['sms'] = array(
				'no' => $data['sms_no'],
				'content' => $data['sms_content'],
				'sms_btn'=> '发送短信'
			);
		}
		
		if(isset($data['phone']) && $data['phone']){
			$result['phone'] = $data['phone'];
			$result['phone_btn'] = '拨打电话';
		}
		
		if(isset($data['url']) && $data['url']){
			$result['url'] = $data['url'];
			$result['url_btn'] = '更多信息';
		}
		
		return $result;
	}
}