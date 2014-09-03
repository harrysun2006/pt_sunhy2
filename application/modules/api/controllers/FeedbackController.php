<?php

/**
 * 意见反馈API
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_FeedbackController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		$this->auth();
	}		
	
	public function indexAction()
	{
		$this->output();
	}
	
	public function createAction()
	{
		$this->needPost();
		
		$this->xmlRoot = 'message';
		
		$content = trim(urldecode($this->getRequest()->getParam('content', '')));
		if ($content) {
			Better_Feedback::insertFeedback(array(
				'type' => 6,
				'content' => '[API]'.$content,
				'contact' => $this->userInfo['email'],
				'dateline' => time()
				));
			$this->data[$this->xmlRoot] = $this->lang->feedback->thankyou;
		} else {
			$this->error('error.feedback.content_required');
		}
		
		$this->output();
	}
}