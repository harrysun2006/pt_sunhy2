<?php

/**
 * Poi对象
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi
{
	protected $params = array();
	
	public function __construct(array $params=array())
	{
		$this->params = $params;
	}
	
	public function __get($name)
	{
		return isset($this->params[$name]) ? $this->params[$name] : null;
	}
	
	public function __set($name, $value)
	{
		$this->params[$name] = $value;
	}
	
	/**
	 * 搜索POI
	 * 
	 * @param $params
	 * @return array
	 */
	public static function search(array $params=array())
	{
		
	}

	/**
	 * 获得poi基本信息
	 * 
	 * @return array
	 */
	protected function getBasic()
	{
		
	}
	
	/**
	 * 获得poi完整信息
	 * 
	 * @return array
	 */
	protected function getFull()
	{
	
	}
	
	/**
	 * 解析poi信息
	 * 
	 * @return array
	 */
	protected function parse()
	{
		
	}
	
	/**
	 * 新增POI
	 * 
	 * @param $params
	 * @return Better_Poi_Element
	 */
	public static function create(array $params)
	{
		
	}
	
	/**
	 * 更新Poi资料
	 * 
	 * @param $params
	 * @return 
	 */
	public function update(array $params)
	{
		
	}	
}