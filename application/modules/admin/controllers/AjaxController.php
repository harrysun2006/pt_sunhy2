<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_AjaxController extends Better_Controller_Admin
{
	protected $do = '';
	protected $output = '';
	protected $error = '';
	
	public function init()
	{
		parent::init();	

		$this->do = $this->getRequest()->getParam('do', '');
	}
	
	public function indexAction()
	{
		
	}
	
	public function userAction()
	{
		switch ($this->do) {
			case 'list':
				$post = $this->getRequest()->isPost() ? $this->getRequest()->getPost() : array();
				$result = Better_Admin_User::getUsers($post);
				
				$this->output = &$result;
				break;
		}
		
		$this->output();
	}
	
	public function blogAction()
	{
		switch ($this->do) {
			case 'list':
				$post = $this->getRequest()->isPost() ? $this->getRequest()->getPost() : array();
				$result = Better_Admin_Blog::getBlogs($post);
					
				$this->output = &$result;
				break;
		}
		
		$this->output();
	}
	
	protected function output()
	{
		$output = json_encode($this->output);
		//$this->getResponse()->setHeader('Content-Length', strlen($output));
		//$this->getResponse()->setHeader('Content-Type', 'application/json');

		$this->getResponse()->sendHeaders();
		
		echo $output;
		exit(0);
	}	
}