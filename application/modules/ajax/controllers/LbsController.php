<?php

/**
 * 修正过的LBS前段服务
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_LbsController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init();	
	}	
	
	public function indexAction()
	{
		$result = Better_Functions::getLL(array(
			'lbs' => $this->getRequest()->getParam('lbs', ''),
			'uid' => $this->uid
			));
			
		$this->output['lon'] = $result['lon'];
		$this->output['lat'] = $result['lat'];
		$this->output['range'] = $result['range'];
		$this->output['error'] = $result['error'];
		
		if (APPLICATION_ENV!='production') {
			$this->output['message'] = $result['message'];
		}

		$this->output();
	}
	
	public function ipAction()
	{
		$result = Better_Service_Ip2ll::parse();
		
		$this->output['lon'] = $result['lon'];
		$this->output['lat'] = $result['lat'];
		$this->output['range'] = '30000';
		
		$this->output();
	}

}