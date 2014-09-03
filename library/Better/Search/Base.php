<?php

/**
 * Better的搜索应用
 * 分为三种搜索途径：
 * 1、MySQL模糊查询
 * 2、Sphinx全文检索（目前server端尚未提供接口）
 * 3、Qbs查询（用来根据坐标范围查找相关内容）
 *
 *
 * @package Better.Search
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Search_Base
{
	protected $params = array(
										'count' => BETTER_PAGE_SIZE,
										'keyword' => '',
										'page' => '1',
										);
	protected $results = array();
	protected $result = array(
										'total' => 0,
										'rows' => array(),
										'count' => 0,
										'pages' => 0,
										'emails' => array(),
										);
	protected $total = 0;
	
	protected function __construct(array $params)
	{
		if (count($params)>0) {
			foreach ($params as $key=>$value) {
				$this->params[$key] = $value;
			}
		}
	}
	
	public function search() 
	{
	}
	
	protected function parseResults()
	{
		
	}
	
}