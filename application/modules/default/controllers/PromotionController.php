<?php

/**
 * 前台用户注册控制器
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class PromotionController extends Better_Controller_Front 
{
	
	public function init()
	{
		parent::init();
		$this->commonMeta();
		
	    $this->view->headScript()->appendFile($this->jsUrl.'/controllers/signup.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
   		
	    $this->view->post = $this->getRequest()->isPost() ? $this->getRequest()->getPost() : array();
	}
	
	/**
	 * 注册表单页面显示
	 *
	 * @return null
	 */
	public function indexAction()
	{
		if ($this->uid>0) {
        	$this->_helper->getHelper('Redirector')->gotoSimple('index','home');
        	exit(0);
        } else {
        	$this->_helper->getHelper('Redirector')->gotoUrl("/index");			
        	exit(0);
        }
		
		$ref = $this->getRequest()->getParam('ref');
		if ($ref) {
			Better_Registry::get('sess')->set('ref_uid', $ref);
		}	
		$mail_partner = $this->getRequest()->getParam('mailpartner');
     	if($mail_partner){
            Better_Registry::get('sess')->set('mail_partner', $mail_partner);
       	}    	
	}	
	public function ipadAction()
	{
		
		if ($this->uid>0) {
        	$this->_helper->getHelper('Redirector')->gotoSimple('index','home');
        	exit(0);
        } else {
        	$this->_helper->getHelper('Redirector')->gotoUrl("/index");			
        	exit(0);
        }
		
		if(Better_Registry::get('sess')->get('hadpromotion')){
			Better_Registry::get('sess')->set('promotionurl','');
			Better_Log::getInstance()->logInfo($_SESSION['promotioncomefrom'],'ipadsucessreg');
		} else {
			Better_Registry::get('sess')->set('promotionurl','/promotion/ipad');
		}
		if(strpos($_SERVER['HTTP_REFERER'],$_SERVER['SERVER_NAME'])===false && !isset($_SESSION['promotioncomefrom'])){					
			$_SESSION['promotioncomefrom'] = $_SERVER['HTTP_REFERER'];
			Better_Log::getInstance()->logInfo("ab".$_SERVER['HTTP_REFERER'],'ipadcome');	
		}
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/promotion/ipad.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));	
		$this->view->uid = $this->uid;
	}
	public function topinvitationAction(){			
		$params['begtm'] = gmmktime(16,0,0,5,15,2011);
		$topinvitation = Better_DAO_Invite::getInstance()->topInvitation($params);
		$result = array();	
		$i= 1;
		Better_Log::getInstance()->logInfo(serialize($topinvitation),'invitation');
		foreach($topinvitation['rows'] as $row){
			if($i>10){
				break;
			}
			$row['userinfo'] = Better_User::getInstance($row['uid'])->getUser();
			$result['rows'][] = $row;
			$i++;
		}
		$result['total'] = $topinvitation['total'];
		echo json_encode($result);
		exit(0);
		
	}
}

?>