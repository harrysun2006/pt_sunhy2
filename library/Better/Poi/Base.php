<?php

/**
 * POI基类
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Base
{
	protected $poiId = 0;
	
	protected function __construct($poiId)
	{
		$this->poiId = $poiId;	
	}
}