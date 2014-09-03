<?php
/**
 * admin 3rdpool controller
 * @author sunhy
 */
class Admin_ThirdpoolController extends Better_Controller_Admin
{
	
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/thirdpool.js?ver='.BETTER_VER_CODE);
		$this->view->title="第三方token池";
		$protocols = Better_Config::getAppConfig()->thirdpool->protocols;
		$this->view->headScript()->prependScript('
		var protocols =\''.$protocols.'\';		
		');
	}

	private static function format($s)
	{
	  if (strlen($s) < 4) return '****';
	  $a = substr($s, 0, 2);
	  $b = substr($s, -2);
	  return $a . '**' . $b;
	}

	public function indexAction()
	{ 
		$params = $this->getRequest()->getParams();
		$result = Better_Admin_Thirdpool::getTokens($params);
		foreach ($result['rows'] as &$row) {
		  $row['#password'] = self::format($row['password']);
		  $row['#oauth_token'] = self::format($row['oauth_token']);
		}		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	public function addAction()
	{
		$params = $this->getRequest()->getParams();
		$uid = isset($params['uid']) ? $params['uid'] : 0;
		$protocol = isset($params['protocol']) ? $params['protocol'] : '';

    $result = false;
		if ($uid && $protocol) {
			$result = Better_Admin_Thirdpool::addToken($uid, $protocol);
		}
		if ($result) $result = true;
		$this->sendAjaxResult($result);		
	}

	public function removeAction()
	{
	  $params = $this->getRequest()->getParams();
	  $uid = isset($params['uid']) ? $params['uid'] : 0;
	  $protocol = isset($params['protocol']) ? $params['protocol'] : '';
	
	  $result = false;
	  if ($uid && $protocol) {
	    $result = Better_Admin_Thirdpool::removeToken($uid, $protocol);
	  }
	  $this->sendAjaxResult($result);
	}

}

?>