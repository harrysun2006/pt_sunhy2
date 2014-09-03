<?php

/**
 * 邀请朋友
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_InvitefriendsController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		
		$this->auth();
	}		
	
	/**
	 * 13.8 邀请号码簿里面的非好友
	 * 
	 * @return
	 */
	public function byphoneAction()
	{
		$this->needPost();
		$this->xmlRoot = 'users';
		
		$data = $this->getRequest()->getRawBody();
		$data = str_replace('decode2=false', '', $data);
		
		if ($data) {
			$results = Better_Mobile_Contacts::decrpt($data);
			
	
			if (count($results)>0 && isset($results['users']['user'])) {
				$phones = array();
				foreach ($results['users'] as $row) {
					Better_Mobile_Contacts::log($this->uid, $row);
					$phones[$row['contact']['content']] = $row;
				}
				
				$searcher = Better_Search::factory(array(
					'what' => 'user',
					'page' => 1,
					'count' => 9999,
					'keyword' => array_keys($phones),
					'method' => 'mysql',
					));
				$result = $searcher->searchCell();
				
				$registered = array();
				foreach ($result['rows'] as $row) {
					$registered[] = $row['cell_no'];
				}						
				
				$results = array_diff(array_keys($phones), $registered);
				foreach ($results as $phone) {
					$this->data[$this->xmlRoot][] = array(
						'user' => $this->api->getTranslator('user_contact')->translate(array(
							'phone' => $phone,
							'data' => &$phones[$phone]
							)),
						);
				}
				
				$this->api->setParam('xml_root', $this->xmlRoot);
				$this->api->setParam('data', $this->data);
				$output = Better_Mobile_Contacts::enc($this->api->output());
								
				$this->getResponse()->setHeader('Content-Length', strlen($output));
				$this->getResponse()->setHeader('Content-Type', 'application/octet-stream');

				echo $output;
				exit(0);
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.invitefriends.invalid_phones');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.invitefriends.invalid_post_data');
		}
		
		$this->output();
	}
	
	/**
	 * 13.6 向Email好友发邀请
	 * 
	 * @return
	 */
	public function byemailAction()
	{
		$ver = $this->getRequest()->getParam('ver', '1');
		
		switch ($ver) {
			case '2':
				$this->_byemailVer2();
				break;
			default:
				$this->_byemailVer1();
				break;
		}
	}
	
	/**
	 * 13.3向IM好友发起邀请
	 * 
	 * @return
	 */
	public function byimAction()
	{
		$this->needPost();
		$this->xmlRoot = 'message';
		$this->output();
		exit(0);
		
		$partner = trim($this->getRequest()->getParam('partner', ''));
		$id = trim($this->getRequest()->getParam('id', ''));
		$password = $this->getRequest()->getParam('password', '');
		$greeting = trim(urldecode($this->getRequest()->getParam('greeting', '')));
		
		$service = new Better_Service_MsnFriends($id, $password);
		$result = $service->revertSearch();
		
		if ($result==1) {
			$results = $service->getResults();
			foreach ($results as $row) {
				Better_Email_Invite::send($email, $this->userInfo);
				$sents++;
			}
					
			$message = str_replace('{COUNT}', $sents, $this->lang->invitefriend->email->sent);
			$this->data[$this->xmlRoot] = $message;	
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.findfriends.partner_error');
		}		
		
		$this->output();
	}
	
	/**
	 * 邀请email好友版本1
	 */
	private function _byemailVer1()
	{
		$this->needPost();
		$this->xmlRoot = 'message';
		
		$partner = strtolower(trim($this->getRequest()->getParam('partner', '')));
		$id = $this->getRequest()->getParam('id', '');
		$password = $this->getRequest()->getParam('password', '');
		$greeting = trim(urldecode($this->getRequest()->getParam('greeting', '')));

		if (preg_match('/@/', $partner)) {
			list($a, $domain) = explode('@', $partner);
		} else if (count(explode('.', $partner))>1) {
			$domain = $partner;
		} else {
			switch ($partner) {
				case 'sina':
				case 'sohu':
				case '163':
				case 'tom':
				case '126':
				case 'gmail':
				case 'yahoo':
					$domain = $partner.'.com';
					break;
				case $this->lang->email_domain->sina_com->name:
					$domain = 'sina.com';
					break;
				case $this->lang->email_domain->sohu_com->name:
					$domain = 'sohu.com';
					break;
				case 'yeah':
					$domain = $partner.'.net';
					break;
				case 'yahoo_com_cn':
				case 'yahoo.com.cn':
					$domain = 'yahoo.com.cn';
					break;
				case 'yahoo_cn':
				case 'yahoo.cn':
					$domain = 'yahoo.cn';
					break;
			}
		}		
	
		if (Better_Functions::checkEmail($id)) {
			list($id, $foobar) = explode('@', $id);
			$foobar && $domain = $foobar;
		}

		if ($partner) {
			if (Better_Functions::checkEmail($id.'@'.$domain)) {
				$service = new Better_Service_EmailContacts($id, $password, $domain);
				$result = $service->revertSearch();
				
				$sents = 0;
				
				switch ($result) {
					case 2:	//	登录失败
						$this->error('error.invitefriends.login_failed');
						break;
					case 3:	//	没有任何联系人
						$this->error('error.invitefriends.no_contacts');
						break;
					case 4:	//	没有符合条件的联系人
						$this->error('error.invitefriends.no_valid_contacts');
						break;
					case 1:
						$results = $service->getResults();
	
						foreach ($results as $row) {
							Better_Email_Invite::send($row['email'], $this->userInfo, $greeting);
							$sents++;
						}
						
						$message = str_replace('{COUNT}', $sents, $this->lang->invitefriend->email->sent);
						$this->data[$this->xmlRoot] = $message;										
						break;
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.invitefriends.invalid_email_id');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.invitefriends.invalid_partner');
		}
		
		$this->output();		
	}
	
	/**
	 * 邀请email好友版本2
	 */
	private function _byemailVer2()
	{
		$this->needPost();
		$this->xmlRoot = 'message';
		$sents = 0;

		$emails = trim(urldecode($this->getRequest()->getParam('emails', '')));
		if ($emails!='') {
			$arr = explode('|', $emails);
			foreach ($arr as $email) {
				if (Better_Functions::checkEmail($email)) {
					Better_Email_Invite::send($email, $this->userInfo);
					$sents++;
				}
			}
			
			$message = str_replace('{COUNT}', $sents, $this->lang->invitefriend->email->sent);
			$this->data[$this->xmlRoot] = $message;			
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.invitefriends.invalid_emails_to_invite');
		}
		
		$this->output();
	}
	
}