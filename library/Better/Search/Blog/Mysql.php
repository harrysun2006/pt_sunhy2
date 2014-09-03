<?php

/**
 * 通过MySQL模糊搜索消息
 *
 * @package Better.Search.Blog
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Search_Blog_Mysql extends Better_Search_Blog_Base
{
	public function __construct(array $params)
	{
		parent::__construct($params);
	}
	
	public function search()
	{
		$this->results = Better_Blog::filteBids(array_unique(Better_DAO_BlogSearch::getInstance()->search($this->params)));
		$this->parseResults();
		
		return $this->result;
	}

}