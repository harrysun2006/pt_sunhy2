<?php

class Market_IndexController extends Better_Controller_Market
{
	protected $output = array();
	
    public function init()
    {
    	parent::init();
    	
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/market/index.js?ver='.BETTER_VER_CODE);
    	
    	if (!$this->sess->market_uid) {
			$this->_helper->getHelper('Redirector')->gotoUrl('http://'.$_SERVER['HTTP_HOST'].'/market/login');
			exit(0);
		}
		
    	$this->commonMeta();	
    }

    public function indexAction()
    {
    	$params = $this->getRequest()->getParams();
    	//$poi_id = $this->getRequest()->getParam('poi_id', 122449);
    	$page = $params['page']? $params['page'] : 1;
    	$page_size = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
    	$status = $params['status']? $params['status'] : '';
    	
    	$poi_id = Better_Config::getAppConfig()->poi->getitlouder->sh->id;
    	$poi_info = Better_Poi_Info::getInstance($poi_id)->getBasic();
    	
    	$para = array(
    		'poi_id'=>$poi_id,
    		'page'=>$page,
    		'page_size'=>$page_size,
    		'status'=>$status,
    		'market_uid'=>$this->sess->get('market_uid')
    	);
    	
    	$results = Better_DAO_Market_Admin_Blog::getAllData($para);
    	
    	$rows = array();
    	foreach ($results['rows'] as $row){
    		$user = Better_User::getInstance($row['uid'])->getUser();
    		$row['user'] = $user;
    		switch($row['type']){
    			case 'checkin':
    				$row['type'] = '签到';
    				break;
    			case 'normal':
    				$row['type'] = '吼吼';
    				break;
    			case 'tips':
    				$row['type'] = '贴士';
    				break;
    			default:
    				break;
    		}
    		
    		if ($row['attach'] && strlen($row['attach'])>=5) {
				$attach = Better_Attachment_Parse::getInstance($row['attach'])->result();
	
				$row['attach_tiny'] = $attach['tiny'];
				$row['attach_thumb'] = $attach['thumb'];
				$row['attach_url'] = $attach['url'];
			} else {
				$row['attach_url'] = $row['attach_tiny'] = $row['attach_thumb'] = '';
			}
    		
    		$rows[] = $row;
    	}
    	
    	$this->view->count = $results['count'];
    	$this->view->poi = $poi_info;
    	$this->view->rows = $rows;
    	$this->view->mid = $this->sess->get('market_uid');
    	$this->view->params = $params;
    }
    
    
    public function checkAction(){
    	$bid = $this->getRequest()->getParam('bid', '');
    	$uid = $this->getRequest()->getParam('uid', '');
    	$poi_id = $this->getRequest()->getParam('poi_id', '');
    	
    	$params = $this->getRequest()->getParams();
    	
    	$market_uid = $this->sess->get('market_uid');
    	$kai_checked = 0;
    	$partner_checked = 0;
    	if($market_uid==1000){
    		$params = array('kai_checked'=>1);
    		$kai_checked = 1;
    	}else if($market_uid==2000){
    		$params = array('partner_checked'=>1);
    		$partner_checked = 1;
    	}
    	$params['last_checked'] = time();
    	
    	$row = Better_DAO_Market_Admin_Blog::getInstance($uid)->get($bid);
    	
    	if($row && $row['bid']){
    		$flag = Better_DAO_Market_Admin_Blog::getInstance($uid)->update($params, $bid);
    	}else{
    		$data = array(
    			'bid'=>$bid,
    			'uid'=>$uid,
    			'kai_checked'=>$kai_checked,
    			'partner_checked'=>$partner_checked,
    			'poi_id'=>$poi_id
    		);
    		$flag = Better_DAO_Market_Admin_Blog::getInstance($uid)->insert($data);
    	}
    	
    	$this->_helper->getHelper('Redirector')->gotoUrl('http://'.$_SERVER['HTTP_HOST'].'/market/index?status=not_check');
    	exit();
    }
    
    
    public function checkallAction(){
    	$bids = $this->getRequest()->getParam('bids', array());
    	$poi_id = $this->getRequest()->getParam('poi_id', '');
    	
    	$market_uid = $this->sess->get('market_uid');
    	
		if($bids && count($bids)>0){
			foreach($bids as $bid){
				$tmp = explode('.', $bid);
				$uid = $tmp[0];
				
				$params = array();
				$kai_checked = 0;
    			$partner_checked = 0;
    			if($market_uid==1000){
    				$params = array('kai_checked'=>1);
    				$kai_checked = 1;
    			}else if($market_uid==2000){
    				$params = array('partner_checked'=>1);
    				$partner_checked = 1;
    			}
    			$params['last_checked'] = time();
    			
    			$row = Better_DAO_Market_Admin_Blog::getInstance($uid)->get($bid);
    			
				if($row && $row['bid']){
    				$flag = Better_DAO_Market_Admin_Blog::getInstance($uid)->update($params, $bid);
    			}else{
    				$data = array(
    					'bid'=>$bid,
    					'uid'=>$uid,
    					'kai_checked'=>$kai_checked,
    					'partner_checked'=>$partner_checked,
    					'poi_id'=>$poi_id
    				);
    				$flag = Better_DAO_Market_Admin_Blog::getInstance($uid)->insert($data);
    			}
			}
		}
		   
		$output['result'] = 1;
		echo json_encode($output);	
    }
    
}