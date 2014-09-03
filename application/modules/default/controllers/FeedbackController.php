<?php
/**
 * 反馈
 */

class FeedbackController  extends Better_Controller_Front{
	
	protected $output=array();
	
	public function init(){
		parent::init();
    	$this->commonMeta();
    	
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/feedback.js', 'text/javascript', array(
    		'defer' => 'defer'
    		));
    	
	}
	
	public function indexAction(){
		
	}

	/**
	 * feedback 提交
	 */
	public function submitAction(){
		$this->output['has_err'] = 1;
		
		$type = $this->getRequest()->getParam('type');
		$content = $this->getRequest()->getParam('content');
		$contact = $this->getRequest()->getParam('contact');
		$uid = $this->getRequest()->getParam('uid')?(int)$this->getRequest()->getParam('uid'):0;

		if (Better_Functions::checkEmail($contact)) {
			if($type && $content){
				$data=array(
					'type' => $type,
					'content' => $content,
					'contact' => $contact,
					'dateline' => time(),
					'uid' =>$uid
				);
					
				$id=Better_Feedback::insertFeedback($data);
				if($id){
					$this->output['has_err'] = 0;
				}
				else{
					$this->output['has_err'] = 'Insert failed.';
				}
			}
			else{
				$this->output['has_err'] = 'Feedback type or content is reqiured.';
			}	
		}else{
			$this->output['has_err'] = $this->lang->error->email_invalid;
		}
		
		$this->output();
	}
	
	
	/**
	 * 输出json数据
	 *
	 * @return null
	 */
	protected function output()
	{
		if (APPLICATION_ENV=='development') {
			$this->output['exec_time'] = $this->view->execTime();
		}
		
		if ($this->error) {
			$this->output['exception'] = $this->error;
		}
		
		$output = Zend_Json::encode($this->output);
		
		$this->getResponse()->sendHeaders();

		echo $output;
		exit(0);
	}
	
}

?>