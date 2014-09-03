<?php

/**
 * 未完成
 *
 * @package Controllers
 */

class NotfinishedController extends Better_Controller_Front
{
	protected $output = array();
	
    public function init()
    {
    	parent::init();
    	$this->commonMeta();
    	
    	$this->view->css = 'index';
    }

    public function indexAction()
    {
        
    }
    
}

