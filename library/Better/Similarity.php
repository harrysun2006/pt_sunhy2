<?php

/**
 * 查询POI附近相似的POIs
 *
 * @package Better
 * @author  mengxf
 *curl http://172.17.20.22/api/lbs/poi_similar.php?pid=363457
 */
class Better_Similarity
{

	public static function getSimilarityPois($poi_id)
	{
		try{
			$url = Better_Config::getFullConfig()->kai->similarity->url?Better_Config::getFullConfig()->kai->similarity->url:'http://lbs.org.cn/api/lbs/poi_similar.php?';
			$xml = simplexml_load_file($url."pid=".$poi_id);
		}catch (Exception $e){			
			Better_Log::getInstance()->logInfo($e->getTraceAsString(), 'similarity_exception');
		}
		return $xml;
	}
}