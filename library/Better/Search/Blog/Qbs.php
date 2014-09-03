<?php

/**
 * 通过Qbs搜索消息
 *
 * @package Better.Search.Blog
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Search_Blog_Qbs extends Better_Search_Blog_Base
{
	
	public function __construct(array $params)
	{
		parent::__construct($params);
		$this->params['begin'] = $this->params['page']*$this->params['count'] - $this->params['count'] + 1;
		$this->params['end'] = $this->params['begin']+$this->params['count'];
	}
	
	public function search()
	{
		$service = Better_Service_Qbs::getInstance();
		$results = $service->getBlogByXY($this->params['lon'], $this->params['lat'], $this->params['w'], $this->params['h'], 1, BETTER_MAX_LIST_ITEMS+1);
		
		if (is_array($results) && count($results)) {
			krsort($results);
			$bids = Better_DAO_Blog::getInstance()->validBids(array_keys($results));
			$this->results = Better_Blog::filteBids($bids);
		} 
		
		$this->parseResults();
		
		return $this->result;
	}
		
}