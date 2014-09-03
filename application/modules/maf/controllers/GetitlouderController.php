<?php

/**
 *大声展大屏
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class Maf_GetitlouderController extends Better_Controller_Front
{
	protected $output = array();
	
    public function init()
    {
    	parent::init();
    	/*
    	$forceRedirect = $this->getRequest()->getParam('force_redirect', 0);
	
		if (!$forceRedirect && Better_Functions::isWap()) {
			$this->_helper->getHelper('Redirector')->gotoUrl('/mobile');
			exit(0);
		}
		 */  	
    	$this->commonMeta();    	
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/getitlouder.js?ver='.BETTER_VER_CODE); 
    	
    }

    public function indexAction()
    {
    	
    }
	public function blogsAction()
    {
    	$page = (int)$this->getRequest()->getParam('page', 1);

    	$bigshow_uid = Better_Config::getAppConfig()->bigshow->uid;
    	$user = Better_User::getInstance($bigshow_uid)->getUserInfo();
    	$now = time();
    	$bjOvertime = Better_Config::getAppConfig()->poi->getitlouder->bj->overtime;
		if ($bjOvertime>$now) {
			$poiId =  Better_Config::getAppConfig()->poi->getitlouder->bj->id;
		} else {
			$shOvertime = Better_Config::getAppConfig()->poi->getitlouder->sh->overtime;
			$poiId = Better_Config::getAppConfig()->poi->getitlouder->sh->id;
		}
		$user = Better_User::getInstance();
		
		//	吼吼
		$return = Better_User::getInstance()->blog()->getSomebody(array(
				'page' => 1,
				'type' => array('normal', 'checkin', 'tips'),
				'page_size' => 13,
				'uid' => $bigshow_uid,
				'ignore_block' => true,
				'count' => 10
				),9);
		
		/*
	
		$return = Better_User::getInstance()->blog()->user_poi_done(array(
					'type' => array('checkin','normal','tips'),
					'poi' => $poiId,
					'page' => 1,
					'uids' =>$bigshow_uid,
					'count' => 10,
					'page_size' => 13
					),9);	
					*/	
		$this->output['rows'] = &$return['rows'];
		$this->output['count'] = $return['count'];
		$this->output['pages'] = Better_Functions::calPages($return['count']);
		$this->output['page'] = $this->page;		
		$this->output['rts'] = &$return['rts'];		
		$this->outputed = true;		
		if (APPLICATION_ENV=='development') {
			$this->output['exec_time'] = $this->view->execTime();
		}
		
		if ($this->error) {
			$this->output['exception'] = $this->error;
		}
		
		$output = json_encode($this->output);

		$this->getResponse()->sendHeaders();

		echo $output;
		exit(0);
    } 
    public function poiblogsAction()
    {
    	$page = (int)$this->getRequest()->getParam('page', 1);

    	$bigshow_uid = Better_Config::getAppConfig()->bigshow->uid;
    	$user = Better_User::getInstance($bigshow_uid)->getUserInfo();
    	$now = time();
    	$bjOvertime = Better_Config::getAppConfig()->poi->getitlouder->bj->overtime;
		if ($bjOvertime>$now) {
			$poiId =  Better_Config::getAppConfig()->poi->getitlouder->bj->id;
		} else {
			$shOvertime = Better_Config::getAppConfig()->poi->getitlouder->sh->overtime;
			$poiId = Better_Config::getAppConfig()->poi->getitlouder->sh->id;
		}
		$user = Better_User::getInstance();	
		$return = Better_User::getInstance()->blog()->getAllBlogs(array(
					'type' => array('checkin','normal','tips'),
					'poi' => $poiId,
					'page' => 1,					
					'count' => 10,
					'page_size' => 10
					),9);		
		$this->output['rows'] = &$return['rows'];
		$this->output['count'] = $return['count'];
		$this->output['pages'] = Better_Functions::calPages($return['count']);
		$this->output['page'] = $this->page;		
		$this->output['rts'] = &$return['rts'];		
		$this->outputed = true;		
		if (APPLICATION_ENV=='development') {
			$this->output['exec_time'] = $this->view->execTime();
		}
		
		if ($this->error) {
			$this->output['exception'] = $this->error;
		}
		
		$output = json_encode($this->output);

		$this->getResponse()->sendHeaders();

		echo $output;
		exit(0);
    } 
}

