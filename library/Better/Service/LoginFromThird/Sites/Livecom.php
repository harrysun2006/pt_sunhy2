<?php

/**
 * 抄送到Live Space
 *
 * @package Better.Service.PushToOtherSites
 * @author leip <leip@peptalk.cn>
 * @NOTE
 * 	1、LiveSpace的api有诸多限制，比如24小时内验证失败达到一个数字（比较小），则会被封（不确定是封了ip还是帐号），然后
 * 		一律返回Access Denied，调试起来比较麻烦
 * 	2、使用LiveSpace的api需要先到LiveSpace去开通Email发布功能，设置Email发布的口令（独立与登录口令），然后将空间名和刚才
 * 		的口令传递给本class（如LiveSpace的空间地址为：http://abcdefg.spaces.live.com，则空间名为abcdefg）
 * 	3、使用LiveSpace的api需要php的xmlrpc扩展
 *
 */

class Better_Service_PushToOtherSites_Sites_Livecom extends Better_Service_PushToOtherSites_Base
{
	protected $apiUrl = 'https://storage.msn.com/storageservice/MetaWeblog.rpc';
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
	}
	
	public function login()
	{
		$this->logined = true;
		
		return $this->_logined;
	}
	
	public function fakeLogin()
	{
		return $this->login();
	}
	
	public function post($msg)
	{
		$client = new Zend_XmlRpc_Client($this->apiUrl);
		$ok = false;
		
		try {
			$client->call('metaWeblog.newPost', array(
				'blogid' => 'MyBlog',
				'username' => $this->_username,
				'password' => $this->_password,
				'content' => array(
					'title' => 'Sync From Better',
					'description' => $msg,
					'dateTime.iso8601' => Better_Functions::date('Y-m-d').'T'.date('H:i:s'),
					'categories' => array(),
					),
				'publish' => true,
			));
			$ok = true;
		} catch(Zend_XmlRpc_Client_FaultException $e) {
			Better_Log::getInstance()->log('SYNC_LIVE_SPACE_FAULT', 'sync');
		} catch(Better_Exception $e) {
			Better_Log::getInstance()->log('SYNC_LIVE_SPACE_FAILED', 'sync');
		}
		
		return $ok;

	}
}