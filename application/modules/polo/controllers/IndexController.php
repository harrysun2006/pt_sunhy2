<?php

/**
 * IndexController
 * 
 * @author Fu Shunkai(fusk@peptalk.cn)
 * @version 
 */

class Polo_IndexController extends Better_Mobile_Front {
	
	public static $maxActivities = 6;
	
	public function init(){
		parent::init();
	}
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
		// If log-in already, go to home page directly
        if ($this->uid > 0) {
        	$this->_redirect('/polo/home');
        } else {
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
        }
	}
	public function kaiAction() {
			
			
	}
}

