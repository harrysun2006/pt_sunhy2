<?php

/**
 * Better消息搜索基类
 * 所有子类只负责具体的搜索，具体的搜索结果是由消息bid组成的数组
 *
 * @package Better.Search.Blog
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Search_Blog_Base extends Better_Search_Base
{

	protected function __construct(array $params)
	{
		parent::__construct($params);
	}
	
	protected function parseResults()
	{
		if (count($this->results)>0) {
			$result = Better_Blog::getByBids($this->results, $this->params['page'], $this->params['count'], $this->params['with_photo']);

			$this->result['rows'] = &$result['rows'];
			$this->result['count'] = $result['count'];
			$this->result['pages'] = Better_Functions::calPages($result['count'], $this->params['count']);
			$this->result['rts'] = &$result['rts'];
		}
		
	}
	
}