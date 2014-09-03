<?php

/**
 * IndexController
 * 
 * @author Fu Shunkai(fusk@peptalk.cn)
 * @version 
 */

#require_once 'Zend/Controller/Action.php';
require_once 'Better/Mobile/Front.php';
require_once 'Better/Blog.php';

class Mobile_IndexController extends Better_Mobile_Front {
	
	public static $maxActivities = 6;
	
	public function init(){
		parent::init();
		//$this->needLogin();
		
		$forceRedirect = $this->getRequest()->getParam('force_redirect', 0);
		if (!$forceRedirect && !Better_Functions::isWap()) {
			$this->_helper->getHelper('Redirector')->gotoUrl('/');
			exit(0);
		}		
	}
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
		// If log-in already, go to home page directly
        if ($this->uid > 0) {
        	$this->_redirect('/mobile/home');
        } else {
        	/*        	 
        	$latest = Better_Blog::getLastest(1, self::$maxActivities);
        	
            $lastrow = array();
        	foreach($latest['rows'] as $rows){
        		try{
        			$rows['message'] =  Better_Blog::wapParseBlogAt($rows['message']);
        		}  catch(Exception $e){
        		
        		}
        		$lastrow[] = $rows;  
        		
        	}          
        	$this->view->timeline = $lastrow;     
        	*/   	 
        }
	}
	public function kaiAction() {
			
			
	}
}

