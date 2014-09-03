<?php

/**
 * 用户未登录时的主页
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class IndexController extends Better_Controller_Front
{
	protected $output = array();
	
    public function init()
    {
    	parent::init();
    	
    	$forceRedirect = $this->getRequest()->getParam('force_redirect', 0);

		if (!$forceRedirect && Better_Functions::isWap()) {
			$this->_helper->getHelper('Redirector')->gotoUrl('/mobile');
			exit(0);
		}
		    	
    	$this->commonMeta();
    	
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/index.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
    	
    	//$this->view->css = 'index';
    }

	public function indexAction()
	{
    	$from_id = $this->getRequest()->getParam('from', 0);
    	$visit_time = time();
    	if($from_id){
    		$data = array('partner_id'=> $from_id,
    				  'visit_time'=> $visit_time,
    				  'visit_ip'=> Better_Functions::getIP()
    				   );
    		Better_DAO_Frompartner::getInstance()->insert($data);
    		
    		Better_Registry::get('sess')->set('web_from', $from_id);
    	}
    	//Better_Log::getInstance()->logInfo($_SERVER['HTTP_REFERER']."--".$_SERVER['SERVER_NAME'],'xxa');
    	// 如果已登录，则定向到home页
		if ($this->uid>0) {
			if ( strpos($_SERVER['HTTP_REFERER'], 'renren.com') !== false ){
				$url = Better_Config::getAppConfig()->base_url . "/tools";
				$this->_helper->getHelper('Redirector')->gotoUrl($url);
			} else {
				$this->_helper->getHelper('Redirector')->gotoSimple('index','home');
			}
        	exit(0);
        } else if(strpos($_SERVER['HTTP_REFERER'],$_SERVER['SERVER_NAME'])===false){
        	$thirdlist = array();
        	$thirdlist[] = array('site'=>'sina','keyword'=>'t.sina.com.cn','homepage'=>'http://weibo.com/kaikai');
        	$thirdlist[] = array('site'=>'sina','keyword'=>'weibo.com','homepage'=>'http://weibo.com/kaikai');
        	$thirdlist[] = array('site'=>'qq','keyword'=>'t.qq.com','homepage'=>'http://t.qq.com/kaierkaier');
        	$thirdlist[] = array('site'=>'163','keyword'=>'t.163.com','homepage'=>'http://t.163.com/kaikai');
        	$thirdlist[] = array('site'=>'renren','keyword'=>'renren.com','homepage'=>'http://page.renren.com/699133235');
        	$fromsite = $_SERVER['HTTP_REFERER'];
        	
        	foreach($thirdlist as $row){
        		$keyword = $row['keyword'];
        		$site = $row['site'];
        		$homepage = $row['homepage'];
        		if(strpos($fromsite,$keyword)===false){
        			continue;
        		} else {
        			//$url = Better_Config::getAppConfig()->base_url . '/login'.$site;
        			$thirdsite = $site;
        			$thirdpage = $homepage;
        			break;
        		} 
        	} 
        	
        	if($thirdsite){       	
        		if($thirdsite=='renren'){
        			$url = Better_Config::getAppConfig()->base_url."/tools";
        		} else {
		        	$url = Better_Config::getAppConfig()->base_url."/login/kai";
		        	$_SESSION['thirdfrom'] = $thirdsite;
		        	$_SESSION['homepage'] = $thirdpage;
        		}
	        	$this->_helper->getHelper('Redirector')->gotoUrl($url);
	        	exit(0);
        	}
        } 
        
        $cityInfo = Better_Functions::getip2city();    
       
        $city =  $cityInfo['live_city'];   
        if($city=='未知'){
             $city = '北京';
        }
        $lonlat  = Better_Service_Geoname::getLonLat( $city );
        if (!$lonlat['lon'] || $lonlat['lat']) {
        	$lonlat['lon'] = 120.62857; //31.31156, 120.62857
        	$lonlat['lat'] = 31.31156;
        }
        
        $cacher = Better_Cache::remote();
		$cacheKey = "poi_major_".$lonlat['lon'].'_'.$lonlat['lat'];
		$cached = $cacher->get($cacheKey);
		if( $cached !== false ){
			$majors = $cached;
		} else {
	    	$majors = Better_Poi_Major::indexMajors(array(
					'lon' => $lonlat['lon'],
					'lat' => $lonlat['lat'],
					'range' => 15000,
					'page' => 1,
					'limit' => 6,
			));
			$cacher->set($cacheKey,$majors,24*3600);
		}
		
		$this->view->majors = $majors;
		
		//市场活动
		if(Better_Config::getAppConfig()->webbanner->switch){
			$params = array(
				'checked' =>1,
				'page' => 1	
			);
			$result = Better_Webbanner::getAll($params);			
			$market = $result['rows'];			
		} else {
			$market = require_once(APPLICATION_PATH.'/../public/market.php');
		}
		$this->view->market = $market;
		$this->view->inIndex = true;
    }
    
    
    public function blogsAction()
    {
		$this->outputed = true;
		$this->output = $this->_getBlog();
		$output = json_encode($this->output);
		$this->getResponse()->sendHeaders();
		echo $output;
		exit(0);
    }
    
    /**
     * 
     * @return unknown_type
     */
    public function _getBlog()
    {
        $page = (int)$this->getRequest()->getParam('page', 1);

		$return = Better_Blog::getIndex($page, 20);

		$_output['rows'] = &$return['rows'];
		$_output['count'] = $return['count'];
		$_output['pages'] = Better_Functions::calPages($return['count']);
		$_output['page'] = $this->page;		
		$_output['rts'] = &$return['rts'];
		
		$this->outputed = true;
		
		if (APPLICATION_ENV=='development') {
			$_output['exec_time'] = $this->view->execTime();
		}
		
		if ($this->error) {
			$_output['exception'] = $this->error;
		} 
		
		return $_output;
    }
    
}

