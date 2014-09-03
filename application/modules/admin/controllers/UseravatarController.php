<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_UseravatarController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile('js/controllers/admin/useravatar.js?ver='.BETTER_VER_CODE);
		$this->view->title="用户头像";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();		
		$params['from']= $params['from']? $params['from'] : date('Y-m-d', time()-BETTER_ADMIN_DAYS-2*3600*24+BETTER_8HOURS);
		$params['to']= $params['to']? $params['to']: date('Y-m-d', time()+BETTER_8HOURS);
		$defaultPageSize =  isset($params['advance']) && $params['advance']? 100: 100;
		$params['page_size'] = $params['page_size'] ? intval($params['page_size']) : $defaultPageSize;
		$result=Better_Admin_User::getUsers($params);
//		if($params['advance'] && !empty($users['rows'])){		//过滤掉不是掌门的用户			
//			$result['rows'] = array();
//			foreach($users['rows'] as $user){				
//				if(Better_DAO_Poi_Major::getInstance()->isMajor($user['uid'])){
//					$result['rows'][]= $user;
//				}
//			}
//			$result['count'] = count($result['rows']);
//		}else{
//			$result = $users;
//		}
		$this->view->params = $params;
		$this->view->rows = $result['rows'];  
		$this->view->count = $result['count'];
	}	
}