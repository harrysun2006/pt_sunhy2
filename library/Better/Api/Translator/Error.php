<?php

/**
 * 错误提示
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Error extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = '';

		$data = &$params['msg'];
		$lang = Better_Language::load();
		//	这里可能取不到$lang变量？
		if ($lang) {
			$lang = $lang->api->toArray();
		} else {
			$lang = Better_Language::loadIt('zh-cn')->api->toArray();
		}
		
		try {
			$codes = explode('.', $data);
			$tmp = array();
			
			foreach ($codes as $code) {
				if (count($tmp)==0 && isset($lang[$code])) {
					$tmp = $lang[$code];
				}
				
				if (count($tmp)>0 && isset($tmp[$code])) {
					$tmp = $tmp[$code];
				}
				
			}
			
			is_array($tmp) ? $result = $tmp[0] : $result = $tmp;
		} catch (Exception $e) {
			$result = $lang['error']['unknown'];
		}
		
		if (is_array($result)) {
			$result = $lang['error']['unknown'];
		}
		return $result;
	}
	
}