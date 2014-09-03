<?php

/**
 * Better用户搜索基类
 * 所有子类只负责具体的搜索，具体的搜索结果是由用户uid组成的数组
 *
 * 注：
 * 由于使用MySQL模糊查询是目前的过渡方式，所以在MySQL查询user的子类中并没有直接返回详细用户数据，
 * 而是和其他搜索方式一样，先返回uid数组，再由本基类去获取详细数据。
 * 这样可以在到时候切换到Sphinx时对基类及子类做最小的修改，缺点是多了一次循环查询。
 *
 * @package Better.Search.User
 * @author leip <leip@peptalk.cn>
 */

class Better_Search_User_Base extends Better_Search_Base
{
	protected $uid = 0;
	
	protected function __construct(array $params)
	{
		parent::__construct($params);
		$this->uid = Better_Registry::get('sess')->uid;
	}
	
	protected function parseResults($columns = array())
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			'page' => 0,
			'pages' => 0,
			);

		if (count($this->results)>0) {
			$count = $this->params['count'] ? $this->params['count'] : BETTER_PAGE_SIZE;
			$page = $this->params['page'] ? (int)$this->params['page'] : 1;
			$tmp = array_chunk($this->results, $count);
			$tmp = isset($tmp[$page-1]) ? $tmp[$page-1] : array(0);
			
			$tmp = Better_DAO_User_Search::getInstance()->getUsersByUids($tmp, 1, $count, '', '', 0, $columns);
			$keys = array();
			foreach ($this->results as $k=>$v) {
				if (is_array($v) && isset($v['uid'])) {
					$keys[$v['uid']] = $k;
				} else {
					$keys[$v] = $k;
				}	
			}

			foreach ($tmp as $k=>$v) {
				$this->result[$k] = $v;
			}
			$this->result['total'] = $this->result['count'] = count($this->results);
			$user = Better_Registry::get('user');

			$rows = $this->result['rows'];
			$this->result['rows'] = array();
			foreach ($rows as $key=>$value) {
				$value['message'] = Better_Blog::dynFilterMessage($value['message']);
				$value['status'] = $value['status'] ? unserialize($value['status']) : array();
				$value['location_tips'] = Better_User::filterLocation($value, 'blog');

				$value['distance'] = $keys[$value['uid']];
				$this->result['rows'][$keys[$value['uid']]] = $user->parseUser($value);
				$this->result['emails'][] = $value['email'];
			}
			
			if (isset($this->params['order']) && $this->params['order']=='distance') {
				ksort($this->result['rows']);
			}
		}

	}
	
	/**
	 * 根据搜索结果的uid结果集，获得具体的搜索详细数据
	 *
	 * @param integer $page
	 * @param integer $pageSize
	 * @return array
	 */
	public function getResults($page=1, $pageSize=20)
	{
		$data = array(
						'count' => 0,
						'rows' => array(),
						);

		if (count($this->results)>0) {
			$data['count'] = count($this->results);
			$data['pages'] = ceil($data['count']/$pageSize);

			if ($page<=$data['pages']) {
				$user = Better_Registry::get('user');
				$rows = array_chunk($this->results, $pageSize);
				$result = Better_DAO_User_Search::getInstance()->getUsersByUids($rows[$page-1]);

				foreach($result['rows'] as $key=>$value) {
					$value['message'] = Better_Blog::dynFilterMessage($value['message']);
					$value['status'] = $value['status'] ? unserialize($value['status']) : array();
					$value['location_tips'] = Better_User::filterLocation($value, 'blog');
					$data['rows'][$key] = $user->parseUser($value);
				}
			}
		}

		return $data;
	}
}