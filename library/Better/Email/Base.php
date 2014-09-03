<?php

class Better_Email_Base
{

	protected $lang = null;
	protected $config = null;
	protected $template = '';
	protected $subject = '';
	protected $receiver = '';
	protected $receiverName = '';
	protected $params = array();
	protected $receivers = array();

	function __construct()
	{
		$this->config = Better_Config::getAppConfig()->email;
		$this->lang = Better_Language::load();
	}

	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	public function set($var, $val=null)
	{
		if (is_array($var) && $val==null) {
			foreach($var as $k=>$v) {
				$this->params[$k] = $v;
			}
		} else {
			if ($val==null) {
				unset($this->params[$var]);
			} else {
				$this->params[$var] = $val;
			}
		}
	}

	public function addReceiver($email, $name='')
	{
		$name=='' && $name=$email;
		$this->receivers[$name] = $email;
	}

	public function send()
	{
		$this->parseTemplate();

		$tr = new Zend_Mail_Transport_Smtp($this->config->sender->host, array(
							'auth' => 'login',
							'username' => $this->config->sender->username, 
							'password' => $this->config->sender->password,
							));
		Zend_Mail::setDefaultTransport($tr);

		$mail = new Zend_Mail('UTF-8');
		if (is_array($this->receivers) && count($this->receivers)>0) {
			foreach($this->receivers as $k=>$v) {
				$mail->addTo($v, $k);
			}
		} else {
			$mail->addTo($this->receiver, $this->receiverName);
		}
		$mail->setFrom($this->config->sender->username, $this->config->sender->sender_name);
		$mail->setSubject($this->subject);
		$mail->setBodyHtml($this->template);
		$mail->send($tr);
	}

	protected function parseTemplate()
	{
		foreach($this->params as $k=>$v) {
			$this->template = str_replace('{'.strtoupper($k).'}', $v, $this->template);
		}
	}
}