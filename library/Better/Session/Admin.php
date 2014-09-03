<?php

/**
 * 后台session实现
 *
 * @package Better.Session
 * @author leip <leip@peptalk.cn>
 */

class Better_Session_Admin extends Better_Session_Base 
{
	public $username = '';
	
	/**
	 * session初始化
	 * 根据获得的sid查询数据库，再根据获得的数据库结果分别处理guest会话和用户会话
	 *
	 * @see library/Better/Session/Better_Session_Base#init()
	 */
	public function init()
	{
		!defined('BETTER_IN_ADMIN') && define('BETTER_IN_ADMIN', true);
		
		$this->namespace = Better_Config::getAppConfig()->session->adminNamespace;
		parent::init('files');
		
		$this->username = $this->get('username');
	}

}

?>