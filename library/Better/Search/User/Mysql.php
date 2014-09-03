<?php

/**
 * 通过MySQL模糊查询来查找用户
 *
 * @package Better.Search.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Search_User_Mysql extends Better_Search_User_Base
{
	
	public function __construct(array $params)
	{
		parent::__construct($params);
	}
	
	public function fuckingSearch()
	{
		$tmp = Better_DAO_User_Search::getInstance()->search($this->params['keyword'], array(
			$this->uid,
			), $this->params);
		foreach ($tmp as $row) {
			$this->results[$row['distance'].'.'.$row['uid']] = $row['uid'];
		}

		if (count($this->results)>0) {
			$tmp = Better_DAO_User_Search::getInstance()->getUsersByUids($this->results, 1, BETTER_MAX_LIST_ITEMS);
			$keys = array_flip($this->results);
			foreach ($tmp as $k=>$v) {
				$this->result[$k] = $v;
			}
			$this->result['count'] = count($this->results);
			$user = Better_Registry::get('user');

			$rows = $this->result['rows'];
			$this->result['rows'] = array();
			foreach ($rows as $key=>$value) {
				$value['message'] = Better_Blog::dynFilterMessage($value['message']);
				$value['status'] = $value['status'] ? unserialize($value['status']) : array();
				$value['location_tips'] = Better_User::filterLocation($value, 'blog');

				$this->result['rows'][$keys[$value['uid']]] = $user->parseUser($value);
				$this->result['emails'][] = $value['email'];
			}
			
			if (isset($this->params['order']) && $this->params['order']=='distance') {
				ksort($this->result['rows']);
			}

			$_rows = array_chunk($this->result['rows'], $this->params['count']);
			$rows = isset($_rows[$this->params['page']-1]) ? $_rows[$this->params['page']-1] : array();
			$this->result['rows'] = &$rows;
			$this->result['pages'] = count($_rows);
		}

		return $this->result;		
	}
	
	public function search()
	{
		$tmp = Better_DAO_User_Search::getInstance()->search($this->params['keyword'], array(
			$this->uid,
			), $this->params);
		foreach ($tmp as $row) {
			$this->results[$row['distance'].'.'.$row['uid']] = $row['uid'];
		}	
		
		$this->parseResults(array('email', 'cell_no'));
		
		if (isset($this->params['order']) && $this->params['order']=='distance') {
			ksort($this->result);
		}

		return $this->result;
	}
	
	protected function parseResultsByKarma()
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
			
			$tmp = Better_DAO_User_Search::getInstance()->getUsersByUids($tmp, 1, $count);
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

				$this->result['rows'][intval($value['karma']).'.'.$value['uid']] = $user->parseUser($value);
				$this->result['emails'][] = $value['email'];
			}
			
			if (isset($this->params['order']) && $this->params['order']=='distance') {
				ksort($this->result['rows']);
			}
		}

	}	
	
	public function searchByKarma()
	{
		$tmp = Better_DAO_User_Search::getInstance()->searchByKarma($this->params['keyword'], array(
			$this->uid,
			), $this->params);
		foreach ($tmp as $row) {
			$this->results[$row['distance'].'.'.$row['uid']] = $row['uid'];
		}	
		
		$this->parseResultsByKarma();
		
		krsort($this->result['rows']);

		return $this->result;
	}	
	
	public function searchEmail()
	{		
		$tmp = Better_DAO_User_Search::getInstance()->searchByEmail($this->params['keyword'], array(
			$this->uid,
			));
		foreach ($tmp as $row) {
			$this->results[$row['uid']] = $row['uid'];	
		}
		
		$this->parseResults(array('email', 'cell_no'));
		
		return $this->result;			
	}
	
	public function searchCell()
	{
		$tmp = (array)$this->params['keyword'];
		$cell = array();
		foreach ($tmp as $row) {
			$row = str_replace('-', '', $row);
			$row = str_replace('.', '', $row);
			$row = str_replace(',', '', $row);
			
			if (preg_match('/^86([0-9]{11})$/', $row)) {
				$cell[] = $row;
			} else if (trim($row)) {
				$cell[] = '86'.$row;
			}
		}
		
		$this->results = Better_DAO_User_Search::getInstance()->searchByCell($cell, array(
			$this->uid,
			));

		$this->parseResults(array('email', 'cell_no'));
		
		return $this->result;					
	}
	public function searchByTop20Karma()
	{
		$tmp =  Better_DAO_User_Search::getInstance()->searchByTop20Karma();		
		foreach ($tmp as $key => $row) {
		    $karma[$key]  = $row['karma'];		   
		}
		array_multisort($karma, SORT_DESC,  $tmp);	
		array_splice($tmp, 40);
		
		foreach ($tmp as $row) {
			$this->results[$row['uid']] = $row['uid'];
		}		
		$this->parseResults();
		return $this->result;
	}
	
	public function searchByTop20Followers()
	{
		/*$tmp =  Better_DAO_User_Search::getInstance()->searchByTop20Followers();		
		foreach ($tmp as $key => $row) {
		    $followers[$key]  = $row['followers'];		   
		}
		array_multisort($followers, SORT_DESC,  $tmp);	
		array_splice($tmp, 40);	
		foreach ($tmp as $row) {
			$this->results[$row['uid']] = $row['uid'];	
		}		
		$this->parseResults();*/
		return $this->result;
	}
	
	public function searchByTop20Friends()
	{
		$tmp =  Better_DAO_User_Search::getInstance()->searchByTop20Friends();		
		foreach ($tmp as $key => $row) {
		    $friends[$key]  = $row['friends'];
		}
		
		array_multisort($friends, SORT_DESC,  $tmp);	
		array_splice($tmp, 40);	
		
		foreach ($tmp as $row) {
			$this->results[$row['uid']] = $row['uid'];	
		}		
		$this->parseResults();
		return $this->result;
	}
	
	public function searchByTop20Blogs()
	{
		$tmp =  Better_DAO_User_Search::getInstance()->searchByTop20Blogs();		
		foreach ($tmp as $key => $row) {
		    $blogsnum[$key]  = $row['blogsnum'];
		}
		array_multisort($blogsnum, SORT_DESC,  $tmp);	
		array_splice($tmp, 40);	
		foreach ($tmp as $row) {
			$this->results[$row['uid']] = $row['uid'];		
		}
		$this->parseResults();
		return $this->results;
	}
	
	public function searchHotUser()
	{
		$cacheKey = 'hotuser';
		
		$cacher = Better_Cache::remote();
		//Better_Cache::remote()->set('hotuser', null);
		$cacher->test($cacheKey) && $results = $cacher->get($cacheKey);

		if (!$results)	
		{	
				$topkarma = $this->searchByTop20Karma();
				$topfollowers = $this->searchByTop20Followers();
				$topfriends = $this->searchByTop20Friends();
				$topblogs = $this->searchByTop20Blogs();
				
				$results = array();
				$topkarmaa = array_values($topkarma['rows']);
				
				foreach ($topkarmaa as $row) {
						$results[$row['uid']] = $row;
				}	
				
				$topfollowersa = array_values($topfollowers['rows']);
				
				foreach ($topfollowersa as $row) {
						$results[$row['uid']] = $row;				
				}	
				$topfriendsa = array_values($topfriends['rows']);		
				foreach ($topfriendsa as $row) {
						$results[$row['uid']] = $row;			
				}	
				
				$topblogsa = array_values($topblogs['rows']);		
				foreach ($topblogsa as $row) {
						$results[$row['uid']] = $row;				
				}
				
				$cacher->set($cacheKey, $results, 86400);
		}		
	    try{
	    	unset($results[$this->uid]);
	    }  catch(Exception $e){	
	    			
		}		
		$rand=array_rand($results,9);
		foreach ($rand as $v) {		 
		  $result[]=$results[$v];
		} 
		
		return $result;
	}
	
	public function searchNewUser()
	{
		$cacheKey = 'newuser';
		$cacher = Better_Cache::remote();
		$cacher->test($cacheKey) && $results = $cacher->get($cacheKey);
		if (!$results)	
		{	
			$results =  Better_DAO_User_Search::getInstance()->searchNewUser();	
			$cacher->set($cacheKey, $results, 3600);
		}
		try{
	    	unset($results[$this->uid]);
	    }  catch(Exception $e){	
	    			
		}
		$rand=array_rand($results,9);
		foreach ($rand as $v) {
		  $result[]=Better_User::getInstance(0)->parseUser($results[$v]);
		} 
		return $result;
	}
	
}