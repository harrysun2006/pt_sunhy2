<?php

/**
 * 阻止
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Public_BlocksController extends Better_Controller_Public
{
	public function init()
	{
		parent::init();
		
		$this->xmlRoot = 'user';
		$this->auth();
	}	

	/**
	 * 阻止某人
	 * 
	 * @return
	 */
	public function createAction()
	{
		$this->xmlRoot = 'user_concise';
		$id = (int)$this->getRequest()->getParam('id', 0);
		$dm_id = (int)$this->getRequest()->getParam('dm_id', 0);
		$rm_id = (int)$this->getRequest()->getParam('rm_id', 0);
		
		if ($id && $id!=$this->uid && !in_array($id, $this->userInfo['blockings'])) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {			
				$result = $this->user->block()->add($id);
				
				if ($result==-1) {
					$this->error('error.blocks.cant_block_sys_user');
				} else if ($result) {
					$this->user->blocks[] = $id;
					$this->data[$this->xmlRoot] = $this->api->getTranslator('user_concise')->translate(array(
						'data' => &$userInfo,
						'userInfo' => &$this->userInfo,
						));
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__.', in_result_code';
					$this->serverError();
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.blocks.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.blocks.invalid_user');
		}
		
		$this->output();
		
	}
	
	/**
	 * 取消阻止某人
	 * 
	 * @return
	 */
	public function destroyAction()
	{
		$this->xmlRoot = 'user_concise';
		$id = (int)$this->getRequest()->getParam('id', 0);
		$dm_id = (int)$this->getRequest()->getParam('dm_id', 0);
		$rm_id = (int)$this->getRequest()->getParam('rm_id', 0);
		
		if ($id && $id!=$this->uid && in_array($id, $this->user->blocks)) {
			$user = Better_User::getInstance($id);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['uid']) {
				$result = $this->user->block()->delete($id);
				if ($result) {
					$this->user->blocks = array_diff($this->user->blocks, $id);
					
					$this->data[$this->xmlRoot] = $this->api->getTranslator('user_concise')->translate(array(
						'data' => &$userInfo,
						'userInfo' => &$this->userInfo,
						));
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->serverError();
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.blocks.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.blocks.invalid_user');
		}
		
		$this->output();
	}
}