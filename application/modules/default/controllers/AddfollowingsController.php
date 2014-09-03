<?php
/**
 * 添加关注
 *
 * @package Controllers
 */
class AddfollowingsController extends Better_Controller_Front 
{
	protected $dispUser = null;
	protected $dispUserInfo = array();
	protected $params = array();

	public function init()
	{
		parent::init();
		$this->commonMeta();
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/addfollowings.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
    	$userInfo = $this->user->getUserInfo();
		$uid = $this->uid;

    	if ($uid) {
    		$this->dispUid = $uid;
    		$this->dispUser = Better_User::getInstance($uid);
    		$this->dispUserInfo = $this->dispUser->getUser();
    		if ($this->dispUserInfo['uid']<=0) {
    			throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
    		} 

    	}else{
    		throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
    	} 

    	//账号状态
    	$user_state = $this->dispUserInfo['state'];
    	if($user_state=='banned'){
			 $this->_helper->getHelper('Redirector')->gotoSimple('accountbanned', 'help');
			 exit();   		
    	}
    	
    	$this->view->userInfo = $this->dispUserInfo;

    	if ($this->dispUserInfo['uid']==$userInfo['uid']) {
    		$this->view->headScript()->prependScript('
    		var dispUser = betterUser;
    		');
    	}     	   	
		$this->userRightBar();

		$spec = 0;
		if ($this->config->sys_spec && $this->dispUserInfo['uid']==BETTER_SYS_UID && $this->uid!=BETTER_SYS_UID) {
			$spec = 1;
		}

    	$this->view->headScript()->prependScript('
    		var Better_Kai_Spec = ' . $spec . ';'
    	);			
    	$this->view->kai_spec = $spec;
		$this->view->needCheckinJs = false;
	}
	
			
	
	public function indexAction()
	{
		
	}
	
}
