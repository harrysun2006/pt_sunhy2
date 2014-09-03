<?php

/**
 * 通过Qbs查找用户
 *
 * @package Better.Search.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Search_User_Qbs extends Better_Search_User_Base
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
		$results = $service->getUserByXY($this->params['lon'], $this->params['lat'], $this->params['w'], $this->params['h'], $this->params['begin'], $this->params['end']);

		if (is_array($results) && count($results)) {
			//	排除自己
			/*if ($this->uid && isset($results[$this->uid])) {
				unset($results[$this->uid]);
			}*/
			$this->results = array_keys($results);
		}
		
		$this->parseResults();
		
		return $this->result;

	}

	public function getResults($begin=0, $end=0)
	{
		$begin==$end && $end==0 ? $end = 20 : $end;
		$pageSize = $end-$begin;
		$page = $begin*$pageSize - $pageSize;
		$page = intval($begin/$pageSize)+1;

		$this->params['page'] = $page;
		$this->params['pageSize'] = $pageSize;
		$this->params['begin'] = $begin;
		$this->params['end'] = $end;
		
		if (method_exists($this, $this->func)) {
			$this->{$this->func}();
		}
		
		return parent::getResults($page, $pageSize);
	}
		
}