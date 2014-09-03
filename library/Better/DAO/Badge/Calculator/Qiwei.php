<?php 
/**
 * 气味图书馆勋章
 * 在以下两个地点有效签到，均能获得【[气味]图书馆】勋章。

 

    上海田子坊店：http://k.ai/poi?id=472489

北京中关村店：http://k.ai/poi?id=753298 / 847374

 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Qiwei extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$poiId = (int)$params['poi_id'];
		
		if ($poiId==472489 || $poiId==753298 || $poiId==847374) {
			$result = true;
		}
		
		return $result;
	}
}