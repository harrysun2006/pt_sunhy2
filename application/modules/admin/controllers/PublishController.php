<?php

/**
 * 发布新版本
 * 
 * @package Controllers
 * @author yangl <yangl@peptalk.cn>
 * 
 */

class Admin_PublishController extends Better_Controller_Admin
{
	public function init()
	{            
		
		parent::init();	
		$this->view->headScript()->appendFile('js/controllers/admin/publish.js?ver='.BETTER_VER_CODE);
		$this->view->title="发布新版本管理";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$result = Better_Admin_Publish::getProducts($params);
		
		$oss = Better_DAO_Admin_Productos::getInstance()->getAll();
		
		$this->view->oss = $oss;
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
		
	}
	
	
	public function phoneAction(){
		$params = $this->getRequest()->getParams();
		$result = Better_Admin_Publish::getPhones($params);
		
		$oss = Better_DAO_Admin_Productos::getInstance()->getAll();
		$brands = Better_DAO_Admin_Downloadbrand::getInstance()->getAll();
		
		$this->view->oss = $oss;
		$this->view->brands = $brands;
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
		
	}
	
	public function updateAction(){
		$result=0;
	
		$params = $this->getRequest()->getParams();
		$pid=$params['pid'];
		$name=$params['name'];
		$desc=$params['desc'];
		$oid=$params['oid'];
		$version=$params['version'];
		$filename=$params['filename'];
		
		$post_date = 0;
		if ($params['post_date']) {
			$post_date = $params['post_date'];
			$y = substr($post_date, 0, 4);
			$m = substr($post_date, 5, 2);
			$d = substr($post_date, 8, 2);	
			$post_date = gmmktime(0, 0, 0, $m, $d, $y)-BETTER_8HOURS;
			
		}

		if(Better_DAO_Admin_Product::getInstance()->get($pid)){
				$data=array(
					'name'=> $name,
					'desc'=> $desc,
					'oid'=> $oid,
					'version'=> $version,
					'filename'=> $filename,
					'postdate'=> $post_date
	        	 );
	       
	       Better_DAO_Admin_Product::getInstance()->update($data, $pid) && $result=1;
	       
		}
		
		$this->sendAjaxResult($result);
	}
	
	
	public function addAction(){
		$result=0;
		
		$params = $this->getRequest()->getParams();
		$name=$params['name'];
		$desc=$params['desc'];
		$oid=$params['oid'];
		$version=$params['version'];
		$filename=$params['filename'];
		
		$post_date = 0;
		if ($params['post_date']) {
			$post_date = $params['post_date'];
			$y = substr($post_date, 0, 4);
			$m = substr($post_date, 5, 2);
			$d = substr($post_date, 8, 2);	
			$post_date = gmmktime(0, 0, 0, $m, $d, $y)-BETTER_8HOURS;
			
		}
		
		if($name!='' && $desc!='' && $version!='' && $filename!=''){
			$data=array(
				'name'=> $name,
				'desc'=> $desc,
				'oid'=> $oid,
				'version'=> $version,
				'filename'=> $filename,
				'postdate'=> $post_date
        	 );
			 Better_DAO_Admin_Product::getInstance()->insert($data) && $result=1;
		
		}
		
		$this->sendAjaxResult($result);
	}
	
	
	public function addphoneAction(){
		$result=0;
		
		$params = $this->getRequest()->getParams();
		$name=$params['name'];
		$desc=$params['desc'];
		$oid=$params['oid'];
		$bid=$params['bid'];
		$img=$params['img'];
		
		if($name!='' && $desc!='' && $img!=''){
			$data=array(
				'name'=> $name,
				'desc'=> $desc,
				'oid'=> $oid,
				'bid'=> $bid,
				'img'=> $img
        	 );
			 Better_DAO_Admin_Phone::getInstance()->insert($data) && $result=1;
		
		}
		
		$this->sendAjaxResult($result);
	}

	
	public function delAction()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$pids = &$post['pids'];
		
		if (is_array($pids) && count($pids)>0) {
			Better_Admin_Publish::delProducts($pids) && $result=1;
		}
		
		$this->sendAjaxResult($result);
	}
	
	public function delphoneAction()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$pids = &$post['pids'];
		
		if (is_array($pids) && count($pids)>0) {
			Better_Admin_Publish::delPhones($pids) && $result=1;
		}
		
		$this->sendAjaxResult($result);
	}
	
	
}