<?php

/**
 * 用户微博收藏
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_FavoritesController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		
		$this->xmlRoot = 'user';
		$this->auth();
	}	
	
	/**
	 * 默认操作
	 * 
	 * @return
	 */
	public function indexAction()
	{
		$this->xmlRoot = 'statuses';
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

	/**
	 * 删除收藏
	 * 
	 * @return
	 */
	public function destroyAction()
	{
		$this->xmlRoot = 'status';
		$this->needPost();
		$bid = $this->getRequest()->getParam('id', 0);

		if (Better_Blog::validBid($bid)) {
			$data = Better_Blog::getBlog($bid);
			if (isset($data['blog']['bid'])) {
				$f = Better_User_Favorites::getInstance($this->uid)->delete($data['blog']['bid']);
				if ($f) {
					$this->user->favorites = array_diff($this->user->favorites, array($data['blog']['bid']));
					$this->data[$this->xmlRoot] = $this->api->getTranslator('status')->translate(array(
						'data' => array_merge($data['blog'], $data['user']),
						'userInfo' => &$this->userInfo
						));
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.favorites.status_id_invalid');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.favorites.status_not_found');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.favorites.status_id_invalid');
		}		
		
		$this->output();
	}
	
	/**
	 * 新建收藏
	 * 
	 * @return
	 */
	public function createAction()
	{
		$this->xmlRoot = 'status';
		$this->needPost();
		$bid = $this->getRequest()->getParam('id', 0);
		
		if (Better_Blog::validBid($bid)) {
			$data = Better_Blog::getBlog($bid);

			if (isset($data['blog']['bid'])) {
				if ($data['blog']['uid']) {
					$f = Better_User_Favorites::getInstance($this->uid)->add($data['blog']['bid'], $data['user']['uid'], $data['blog']['type']);
					if ($f===true) {
						$this->user->push('favorites', $data['blog']['bid']);

						$this->data[$this->xmlRoot] = $this->api->getTranslator('status')->translate(array(
							'data' => array_merge($data['blog'], $data['user']),
							'userInfo' => &$this->userInfo
							));
					} else if ($f===0) {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.favorites.already_favorite');
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.favorites.failed');
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.favorites.cant_self');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.favorites.status_not_found');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.favorites.status_id_invalid');
		}		
		
		$this->output();
	}
}