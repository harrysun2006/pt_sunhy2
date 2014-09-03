<?php

/**
 * 勋章API
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Public_BadgeController extends Better_Controller_Public
{
	public function init()
	{
		parent::init();
		$this->auth();
		
		$this->xmlRoot = 'badges';
	}		
	
	/**
	 * 10.1 可用勋章列表
	 * 
	 * @return
	 */
	public function allAction()
	{

		
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $this->userInfo;
			
			if ($userInfo['uid']) {
				$rows = $user->badge()->getMyBadges();
				foreach ($rows as $row) {
					$this->data[$this->xmlRoot][] = array(
						'badge' => $this->api->getTranslator('badge')->translate(array(
							'data' => &$row,
							'userInfo' => &$userInfo,
							)),
						);
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__.', in_userinfo_invalid';
				$this->error('error.badge.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__.', in_param_invalid';
			$this->error('error.badge.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 10.2 浏览勋章详情
	 * 
	 * @return
	 */
	public function showAction()
	{
		$this->xmlRoot = 'badge';
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id>0) {
			$badge = Better_Badge::getBadge($id);
			try {
				$data = $this->user->badge()->getBadge($id);
				$this->data[$this->xmlRoot] = $this->api->getTranslator('badge')->translate(array(
					'data' => &$data,
					));
			} catch (Exception $e) {
				$this->errorDetail = __METHOD__.':'.__LINE__.', in_catch_exception';
				$this->error('error.badge.invalid_badge');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__.', in_badge_param';
			$this->error('error.badge.invalid_badge');
		}
		
		$this->output();
	}
}