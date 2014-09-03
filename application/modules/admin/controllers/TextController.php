<?php

/**
 * 
 * @package Controllers
 * @author yangl
 * 
 */

class Admin_TextController extends Better_Controller_Admin
{
	public function init()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/text.js?ver='.BETTER_VER_CODE);
		$this->view->title="所有最新文本";		
		
		parent::init();	
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$params['checked'] = '';
		$params['page_size'] = isset($params['page_size'])? $params['page_size'] : 50;
		
		$params['from']= $params['from']? $params['from'] : date('Y-m-d', time()-BETTER_ADMIN_DAYS+BETTER_8HOURS);
		$params['to']= $params['to']? $params['to']: date('Y-m-d', time()+BETTER_8HOURS);
		
		$result = Better_Admin_Alltext::getAlltexts($params);
		
		$this->view->count = $result['count'];
		$this->view->rows = $result['rows'];
		$this->view->params = $params;

	}
	
	
	public function delAction(){
		$result = $result1 = $result2 = $result3 = 1;
		$post = $this->getRequest()->getPost();
		$mids = &$post['ids'];
		$bids = &$post['bids'];
		$fids = &$post['fids'];
		
		if (is_array($bids) && count($bids)>0) {
			$result1 = Better_Admin_Blog::delBlogs($bids);
		}
	
		if (is_array($mids) && count($mids)>0) {
			$result2 = Better_Admin_Dmessage::delReceived($mids);
		}
		
		if(is_array($fids) && count($fids)>0){
			//$result3 = Better_Admin_Dmessage::delSended($fids);
		}
		
		if(!$result1 || !$result2 || !$result3){
			$result = 0;
		}
		
		$this->sendAjaxResult($result);
	}
	
	
	public function rtemailAction(){
		$result = 0;
		$receiver = $this->getRequest()->getParam('receiver', '');
		$content =  $this->getRequest()->getParam('content', '');
		$subject = 'Kai转发的问题';
		
		if($receiver){
			$reveiver_arr = explode(';', $receiver);
			
			$mailer = new Better_Email();
			$mailer->setSubject($subject);
			$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/'.Better_Registry::get('language').'/admin_rt.html');
			
			if(is_array($reveiver_arr)){
				foreach($reveiver_arr as $val){
					$val = trim($val);
					if($val){
						$mailer->addReceiver($val, $val);
						$mailer->set(array('CONTENT'=>$content));
						$mailer->send() && $result = 1;	
					}
				}
			}else{
				$reveiver_arr = trim($reveiver_arr);
				if($reveiver_arr){
					$mailer->addReceiver($reveiver_arr, $reveiver_arr);
					$mailer->set(array('CONTENT'=>$content));
					$mailer->send() && $result = 1;	
				}
			}
			
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('通过邮件转发：<br>'.$content.'<br>给：'.$receiver , 'rt_by_email');
			
		}
		
		$this->sendAjaxResult($result);
		
	}

}