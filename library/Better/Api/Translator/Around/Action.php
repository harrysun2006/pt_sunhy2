<?php

/**
 * 附近 动作
 * 
 * @package Better.Api.Translator.User
 * @author yangl
 *
 */
class Better_Api_Translator_Around_Action extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = $params['data'];
		$result = array();
		
		if(isset($data['id'])){
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
			
			if(isset($data['detail_url']) && $data['detail_url']){
				$result['url'] = $data['detail_url'];
				$result['url_btn'] = '详情';
			}
			
		}
		
		return $result;
	}
}