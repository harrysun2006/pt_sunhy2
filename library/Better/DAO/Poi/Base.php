<?php

/**
 * POI的数据操作基类
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Poi_Base extends Better_DAO_Base
{
	
	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi';
		$this->priKey = 'poi_id';
		$this->orderKey = &$this->priKey;
	}
}