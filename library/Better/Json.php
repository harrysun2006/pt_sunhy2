<?php

/**
 * Json处理
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Json extends Zend_Json
{
	
	public static function xml2array($xml)
	{
        $simpleXmlElementObject = simplexml_load_string($xml);
        if ($simpleXmlElementObject == null) {
            throw new Zend_Json_Exception('Function fromXml was called with an invalid XML formatted string.');
        }

        $resultArray = self::_processXml($simpleXmlElementObject, true);
        
		return $resultArray;		
        
	}
}