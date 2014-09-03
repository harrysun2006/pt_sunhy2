<?php

/**
 * 用户微博收藏
 * 
 * @package
 * @author 
 *
 */
class Public_FavoritesController extends Better_Controller_Public
{
	public function init()
	{
		parent::init();
		
		$this->xmlRoot = 'statuses';
		$this->auth();
	}	
	
	/**
	 * 默认操作
	 * 
	 * @return
	 */
	public function indexAction()
	{
		
		$username = $this->getRequest()->getParam('id', '');
		
		if ($username=='') {
			$dispUserInfo = &$this->userInfo;
		} else {
			$dispUserInfo = Better_User::getInstance()->getUserByUsername($username);
		}

		if ($dispUserInfo['uid']) {
			$return = Better_User::getInstance($dispUserInfo['uid'])->favorites()->all($this->page, $this->count, array(
				'normal', 'tips'
				));
			$i = 0;
			foreach ($return['rows'] as $row) {
				$this->data[$this->xmlRoot][$i++] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.favorites.user_not_found');
		}		
		
		$this->output();
	}
}