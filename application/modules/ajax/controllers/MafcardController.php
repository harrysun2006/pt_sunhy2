<?php

/**
 * 获得卡片
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_MafcardController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init(false);	
	}		
	
	public function indexAction()
	{		
		$post = $this->getRequest()->getPost();			
    	$data = Better_Mafcard::dogetMafCard($post);
    	if($data['result'] == 1){    		
    		$this->output['has_err'] = 0;    			
		} else {
			$this->output['has_err'] = $data['mafcard'];					
		} 
		$this->output();
	}
	
}