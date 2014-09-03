<?php

/**
 * 宝物API
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_TreasuresController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		$this->auth();
	}		
	
	/**
	 * 11.16 用户宝物列表
	 * 
	 * @return
	 */
	public function allAction()
	{
		$this->xmlRoot = 'treasures';
		
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
		if ($id) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				$ts = $user->treasure()->getMyTreasures(true);
				foreach ($ts as $row) {
					$this->data[$this->xmlRoot][] = array(
						'mytreasure' => $this->api->getTranslator('mytreasure')->translate(array(
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
	
}