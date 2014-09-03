<?php

/**
 * 用户踪迹
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class TraceController extends Better_Controller_Front 
{
	public function init()
	{
		parent::init();
		$this->commonMeta();
	}

	public function indexAction()
	{
		exit(0);
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/trace.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
		
		$uid = trim( $this->getRequest()->getParam('uid', '' ) );
		$page = $this->getRequest()->getParam('page', 1);
		$pagecount = 50;
		
		$total = 0;
		$pages = 0;
		$result = array();
		$userInfo = array();
		
		if($this->uid && $this->uid!=$uid){
			$this->view->headScript()->prependScript('
				var inOther_trace = true;
			');
		}else{
			$this->view->headScript()->prependScript('
				var inOther_trace = false;
			');
		}
		
		$userObj = $this->uid ? $this->user : Better_User::getInstance(BETTER_VIRTUAL_UID);
		
		if ($uid) {
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUserInfo();
			
			$reg_date = date('Y.m.d',$userInfo['regtime']+8*3600);
			$total_days = floor((time()- $userInfo['regtime'])/(24*3600));
			
			$cache = Better_Cache::remote();
			$cacheKey = 'trace_'.$uid; 
			if(!$cache->test($cacheKey)){
				$result = $userObj->checkin()->someDaysCheckinedPois(array(
					'uid' => $uid,
					'page' => $page,
					'pagecount' => $pagecount,
					'days'=> $total_days,
					'reg_time'=> $userInfo['regtime']
				));
		
				$clusters = Better_Trace::clusterMarkers($result['rows']);
				
				$cache->set($cacheKey.'_result', $result, 3600);
				$cache->set($cacheKey, $clusters, 3600);
			}else{
				$result = $cache->get($cacheKey.'_result');
				$clusters = $cache->get($cacheKey);
			}
			
			$this->view->headScript()->prependScript('
				var uid = '.$uid.';
				var rows = '.json_encode(Better_Output::filterPois($result['rows'])).';
				var user = '.json_encode(Better_Output::filterUser($userInfo)).';
				var clusters = '.json_encode($clusters).';
				var total_days = '.$total_days.';
			');
			
			$total = $result['count'];
			//$pages = ceil($total/$pagecount);
		}
		
		$this->view->page = $page;
		//$this->view->pages = $pages;
		$this->view->trace_total = $total;
		$this->view->trace_user = $userInfo;
		$this->view->trace_rows = $result['rows'];
		$this->view->needCheckinJs = true;
		$this->view->inTrace = true;
		$this->view->reg_date = $reg_date;
	}
	
	
	public function shorturlAction(){
		$return = array();
		$urls = $this->getRequest()->getParam('urls', array());
		
		foreach($urls as $url){
			$result = Better_Url::toDb($url);
			$return[$url] = $result;
		}
		
		$output = json_encode($return);
		echo $output;
		exit(0);
	}
	
}

?>
