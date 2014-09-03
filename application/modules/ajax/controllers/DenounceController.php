<?php

/**
 * 举报
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_DenounceController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init();	
	}		
	
	public function indexAction()
	{
		$this->output['has_err'] = 1;
		
		$nickname = $this->getRequest()->getParam('nickname');
		$content = $this->getRequest()->getParam('content');
		$reason = $this->getRequest()->getParam('reason');
				
		$data=array(
			'denounce_nickname' => $nickname,
			'denounce_content' => $content,
			'denounce_reason' => $reason,
			'uid' => $this->uid
		);
				
		$id=Better_DAO_Denounce::getInstance()->insert($data);
		if($id){
			$this->output['has_err'] = 0;
		}
		
		$this->output();
						
	}
}