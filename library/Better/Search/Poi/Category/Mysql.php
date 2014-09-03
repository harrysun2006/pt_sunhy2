<?php

/**
 * 使用Mysql搜索Poi分类
 * 
 * @package Better.Search.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Search_Poi_Category_Mysql extends Better_Search_Poi_Category_Base
{
	public function __construct(array $params)
	{
		parent::__construct($params);
	}
	
	public function search()
	{
		$result = Better_DAO_Poi_Category_Search::getInstance()->search($this->params);

		$this->result['total'] = $result['total'];
		$this->result['rows'] = &$result['rows'];

		if (count($result['rows'])>0) {
			$data['pages'] = Better_Functions::calPages(count($this->results), $this->param['count']);
			if ($this->params['page']<=$data['pages']) {
				$result = Better_Blog::getByBids($this->results, $this->params['page'], $this->params['count'], $this->params['withPhoto']);

				$data['rows'] = &$result['rows'];
			}
			$this->result['pages'] = Better_Functions::calPages($data['count'], $this->keyword['count']);
		}

		return $this->result;
	}

}