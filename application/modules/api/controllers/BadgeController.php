<?php

/**
 * 勋章API
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_BadgeController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		$this->auth();
	}		
	
	/**
	 * 
	 * 分页取用户勋章
	 */
	public function pageAction()
	{
		$this->xmlRoot = 'badges';
		
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		$family = $this->getRequest()->getParam('family', 'all');

		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				if ($userInfo['uid']!=BETTER_SYS_UID) {
					$rows = $user->badge()->getByFamily($family);
					$tmp = array_chunk($rows, $this->count);
					if (isset($tmp[$this->page-1])) {
						foreach ($tmp[$this->page-1] as $row) {
							$this->data[$this->xmlRoot][] = array(
								'badge' => $this->api->getTranslator('badge')->translate(array(
									'data' => &$row,
									'userInfo' => &$userInfo,
									)),
								);
						}
					}
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
	 * 10.1 可用勋章列表
	 * 
	 * @return
	 */
	public function allAction()
	{
		$this->xmlRoot = 'badges';
		
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				if ($userInfo['uid']!=BETTER_SYS_UID) {
					$rows = $user->badge()->getMyBadges();
					foreach ($rows as $row) {
						$this->data[$this->xmlRoot][] = array(
							'badge' => $this->api->getTranslator('badge')->translate(array(
								'data' => &$row,
								'userInfo' => &$userInfo,
								)),
							);
					}
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
		$badgeSize = (int)$this->getRequest()->getParam('badge_size', '300');
		
		if ($id>0) {
			$badge = Better_Badge::getBadge($id);
			
			try {
				$data = $this->user->badge()->getBadge($id);
				$exchange = $data;
				$exchange['note'] = Better_Language::loadDbKey('help_tips', $exchange);
				$this->data[$this->xmlRoot] = $this->api->getTranslator('badge')->translate(array(
					'data' => &$data,
					'exchange' => &$exchange,
					'big_badge' => true,
					'badge_size' => $badgeSize
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
	
	/**
	 * 勋章兑换
	 * 
	 * @return
	 */
	public function redeemAction()
	{
		$this->xmlRoot = 'message';
		$id = (int)$this->getRequest()->getParam('id', 0);
		$code = $this->getRequest()->getParam('code', 'gorush');

		if ($id>0) {
			$data = $this->user->badge()->getBadge($id);
			
			if ($data['exchanged']) {
				$this->errorDetail = __METHOD__.':'.__LINE__.', in_badge_exchange';
				$this->error('error.badge.has_exchanged');
			} else {
				$result = $this->user->badge()->exchange($id, $code);
				$codes = &$result['codes'];

				switch ($result['code']) {
					case $codes['SUCCESS']:
						$this->data[$this->xmlRoot] = $this->lang->badge->exchange_success;
						break;
					case $codes['EXPIRED']:
						$this->errorDetail = __METHOD__.':'.__LINE__.', in_badge_param';
						$this->error('error.badge.expired');
						break;
					case $codes['NO_REMAINS_LEFT']:
						$this->errorDetail = __METHOD__.':'.__LINE__.', in_badge_param';
						$this->error('error.badge.no_remains_left');						
						break;
					case $codes['EXCHANGED']:
						$this->errorDetail = __METHOD__.':'.__LINE__.', in_badge_param';
						$this->error('error.badge.has_exchanged');						
						break;
					case $codes['NOT_HAVE']:
						$this->errorDetail = __METHOD__.':'.__LINE__.', in_badge_param';
						$this->error('error.badge.not_have');						
						break;
					case $codes['CODE_WRONG']:
						$this->errorDetail = __METHOD__.':'.__LINE__.', in_badge_param';
						$this->error('error.badge.code_wrong');						
						break;
					case $codes['FAILED']:
					default:
						$this->serverError();
						break;					
				}
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__.', in_badge_param';
			$this->error('error.badge.invalid_badge');
		}
		
		$this->output();
	}
	
	/**
	 * 获得用户的勋章册
	 */
    public function albumAction()
    {
    	$this->xmlRoot = 'badge_album';
		
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		$preview = (int)$this->getRequest()->getParam('preview', 9999);
		
				
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				if ($userInfo['uid']!=BETTER_SYS_UID) {
					$album = $user->badge()->getAlbum(true, $preview);
					foreach ($album as $id => $rows) {
						if ($rows['unlocked'] == 0) {
							continue;
						}
						$family['id'] = $id;
						$family['label'] = Better_Badge::$families[$id];
						$family['total'] = $rows['totoal'];
						$family['unlocked'] = $rows['unlocked'];
						$family['badges'] = array();
						foreach ($rows['badges'] as $row) {
							$family['badges'][] = array(
							'badge' => $this->api->getTranslator('badge')->translate(array(
								'data' => &$row,
								'userInfo' => &$userInfo,
								)),
							);
						}
						$this->data[$this->xmlRoot][] = array('family' => $family);
					}
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
}