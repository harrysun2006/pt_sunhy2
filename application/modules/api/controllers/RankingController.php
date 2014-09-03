<?php

/**
 * 排行榜API
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_RankingController extends Better_Controller_Api
{
	public $weekRp = true;
	
	public function init()
	{
		parent::init();
		$this->auth();
		
		$global = $this->getRequest()->getParam('global', 'false') == 'true' ? true : false ;
		$this->weekRp = !$global;
	}		
	
	public function karmaAction()
	{
		$this->xmlRoot = 'karma_ranking';
		
		$this->data[$this->xmlRoot] = array(
			'myfriends' => array(),
			'mycity' => array(),
			'global' => array()
			);
		
		$results = $this->user->ranking()->karmaMyFriends(array(
			'page' => $this->page,
			'page_size' => $this->count
			));
			
		foreach ($results['rows'] as $row) {
			$this->data[$this->xmlRoot]['myfriends'][] = array(
				'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);			
		}		
		
		$results = $this->user->ranking()->karmaMyCity(array(
			'page' => $this->page,
			'page_size' => $this->count
			));
			
		foreach ($results['rows'] as $row) {
			$this->data[$this->xmlRoot]['mycity'][] = array(
				'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);			
		}		
		
		$results = $this->user->ranking()->karmaGlobal(array(
			'page' => $this->page,
			'page_size' => $this->count
			));
			
		foreach ($results['rows'] as $row) {
			$this->data[$this->xmlRoot]['global'][] = array(
				'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);			
		}				
		
		$this->output();
	}
	
	/**
	 * 
	 * 好友Karma排行
	 */
	public function karmamyfriendsAction()
	{
		$this->xmlRoot = 'users';
		
		if ($this->weekRp) {
			$results = $this->user->ranking()->karmaWeekMyFriends(array(
				'page' => $this->page,
				'page_size' => $this->count
				));
							
		} else {
			$results = $this->user->ranking()->karmaMyFriends(array(
				'page' => $this->page,
				'page_size' => $this->count
				));
		}
			
		foreach ($results['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);			
		}
		
		$this->output();
	}
	
	/**
	 * 
	 * 同城Karma排行
	 */
	public function karmamycityAction()
	{
		$this->xmlRoot = 'users';
		
		
		if ($this->weekRp) {
			$results = $this->user->ranking()->karmaWeekMyCity(array(
				'page' => $this->page,
				'page_size' => $this->count
				));	
		} else {
			$results = $this->user->ranking()->karmaMyCity(array(
				'page' => $this->page,
				'page_size' => $this->count
				));			
		}

		foreach ($results['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);			
		}
		
		$this->output();		
	}
	
	/**
	 * 
	 * 全局Karma排行
	 */
	public function karmaglobalAction()
	{
		$this->xmlRoot = 'users';
		if ($this->weekRp) {
			$results = $this->user->ranking()->karmaWeekGlobal(array(
				'page' => $this->page,
				'page_size' => $this->count
				));
		} else {
			$results = $this->user->ranking()->karmaGlobal(array(
				'page' => $this->page,
				'page_size' => $this->count
				));
		}
			
		foreach ($results['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);			
		}
		
		$this->output();		
	}	
}
