<?php

/**
 * POI促销
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Poi_Notification extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();

		$data = &$params['data'];
		if (isset($data['poi_id'])) {
			$result['content'] = Better_Functions::cleanBr($data['content']);
			
			$imageUrl = $data['image_url'];
			list($a, $b) = explode('.', $imageUrl);
			if (is_numeric($a) && is_numeric($b)) {
				$attach = Better_Attachment_Parse::getInstance($imageUrl)->result();
				$imageUrl = $attach['url'];
			}
			
			$result['image_url'] = $imageUrl;
		}	
			
		return $result;
	}
}