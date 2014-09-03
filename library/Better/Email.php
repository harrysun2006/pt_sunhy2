<?php

/**
 * Email发送
 *
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Email
{

	protected $lang = null;
	protected $config = null;
	protected $template = '';
	protected $subject = '';
	protected $receiver = '';
	protected $receiverName = '';
	protected $params = array();
	protected $uid = 0;
	protected $type = '';

	function __construct($uid=0)
	{
		$this->config = Better_Config::getAppConfig()->email;
		$this->lang = Better_Language::load();
		$this->uid = $uid ? $uid : Better_Registry::get('sess')->getUid();
	}

	/**
	 * 设置邮件主题
	 *
	 * @param string $subject
	 * @return null
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
	}
	
	/**
	 * 设置邮件类型
	 * @param unknown_type $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * 设置邮件模板，一般指定一个语言包文件夹下的某个文件
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function setTemplate($tpl)
	{
		$this->template = $tpl;
	}

	/**
	 * 设置一个参数
	 *
	 * @param misc $var
	 * @param misc $val
	 * @return array
	 */
	public function set($var, $val=null)
	{
		if (is_array($var) && $val==null) {
			foreach ($var as $k=>$v) {
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

	/**
	 * 增加一个收件人
	 *
	 * @param string $receiver
	 * @param string $name
	 * @return null
	 */
	public function addReceiver($receiver, $name='')
	{
		$this->receiver = $receiver;
		$this->receiverName = $name=='' ? $receiver : $name;
	}

	/**
	 * 发送邮件
	 *
	 * @return unknown_type
	 */
	public function send($uid=null)
	{
		if ($uid!=null) {
			$this->uid = intval($uid);
		}
		
		$this->parseTemplate();
		
		$receiver = is_array($this->receivers) && count($this->receivers)>0
								? serialize($this->receivers)
								: serialize(array(
													$this->receiverName => $this->receiver
													));

		Better_DAO_EmailQueue::getInstance($this->uid)->insert(array(
			'uid' => $this->uid,
			'receiver' => $receiver,
			'body' => $this->template,
			'queue_time' => time(),
			'subject' => $this->subject,
			'go_smtp' => $this->receiverIsYahoo($receiver)
			));

		return true;

	}
	
	
	/**
	 * 发送邮件2
	 * 进common库的队列
	 */
	public function send2()
	{
		$this->parseTemplate();
		
		$receiver = is_array($this->receivers) && count($this->receivers)>0
								? serialize($this->receivers)
								: serialize(array(
													$this->receiverName => $this->receiver
													));

		Better_DAO_EmailCommonQueue::getInstance()->insert(array(
			'uid' => $this->uid,
			'receiver' => $receiver,
			'body' => $this->template,
			'queue_time' => time(),
			'subject' => $this->subject,
			'go_smtp' => $this->receiverIsYahoo($receiver),
			'type' =>$this->type
			));
		
		return true;

	}

	/**
	 * 解析邮件模板，替换一些特殊符号
	 *
	 * @return null
	 */
	protected function parseTemplate()
	{
		if (file_exists($this->template)) {
			$this->template = file_get_contents($this->template);
		}

		foreach ($this->params as $k=>$v) {
			$this->template = str_replace('{'.strtoupper($k).'}', $v, $this->template);
		}
	}
	
	/**
	 * 判断是不是yahoo的邮箱
	 * 
	 * @param unknown_type $receiver
	 */
	protected function receiverIsYahoo($receiver)
	{
		list($user, $domain) = explode('@', $receiver);
		
		return preg_match('/yahoo/', $domain);
	}
}