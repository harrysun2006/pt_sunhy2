<?php

/**
 * poi优惠的action
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Coupon_Action extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];

		$result = array();
		
		if (isset($data['nid'])) {
			$result['sms'] = array(
				'no' => $data['sms_no'],
				'content' => $data['sms_content'],
				'sms_btn'=> '发送短信'
				);
				
			$result['url'] = $data['url'];
			$result['phone'] = $data['phone'];
			
			$result['phone_btn'] = '拨打电话';
			$result['url_btn'] = '详情';
		}
		
		return $result;
	}
}