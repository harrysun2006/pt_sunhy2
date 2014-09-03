<?php

/**
 * ToolsController
 * 
 * @author
 * @version 
 */

//require_once 'Zend/Controller/Action.php';

class Polo_ToolsController extends Better_Mobile_Front {
	/**
	 * The default action - show the home page
	 */
	private static $maxActivities = 6;
	public function indexAction() {
		
		$this->view->menuos = $this->lang->global->index->choice_os;
		$this->view->menubrand = "<a href=\"/polo/tools/brand\">".$this->lang->global->index->choice_brand."</a>";		
	}
	public function brandAction() {
		$page = (int)$_GET['page'];	
		        	
       	if (!$page )
			$page = 1;		
			
		$this->view->menuos = "<a href=\"/polo/tools?cat=os\">".$this->lang->global->index->choice_os."</a>";
		
		$this->view->menubrand = $this->lang->global->index->choice_brand;
		$brands = Better_Download::getAllBrand();		
		for($i=0;$i<count($brands);$i++){
			$brands[$i]['desc'] = Better_Registry::get('language')=='en' ? $brands[$i]['name'] : $brands[$i]['desc'];
		}		
		$this->view->brands = $brands;
		
	}
	public function phoneAction() {
		$page = (int)$_GET['page'];
		$brandid = $_GET['bid'];
		        	
       	if (!$page )
			$page = 1;
		if (!$brandid )
			$brandid = 1;
		
		$this->view->menuos = "<a href=\"/polo/tools?cat=os\">".$this->lang->global->index->choice_os."</a>";		
		$this->view->menubrand = $this->lang->global->index->choice_brand;
		$latest = Better_Download::getPhoneByBidWap(array(
					'page' => $page,
					'bid' => $brandid,
					), 100);
		$this->view->brands = $brands;
		$total = (int)($latest['count']);	
		$this->view->start = ($page - 1)* self::$maxActivities + 1;
		$this->view->phone = $latest['rows'];
		$brand = Better_Download::getOneBrand($brandid);
		$tempbrandname =  Better_Registry::get('language')=='en' ? $brand['name'] : $brand['desc'];
		$this->view->brandurl = "<a href=\"/polo/tools/brand\">".$this->lang->global->index->choice_brand."</a>->".$tempbrandname;

				
		if( $total > $page * 100 )
	    	$this->view->urlNext = "<a href=\"/polo/tools/phone?bid=".$brandid."&page=".($page + 1)."\">".$this->lang->mobile->pager->next."</a>";
	    if ( $page > 1 )
	    	$this->view->urlPrev = " <a href=\"/polo/tools/phone?bid=".$brandid."&page=".($page - 1)."\">".$this->lang->mobile->pager->pre."</a>";	
	            
	}
	
	public function chooseosAction(){
		
		$os = $_GET['os'];	
		$os = str_replace(';','',$os);
		$os = str_replace(':','',$os);		
		if(!$os)
			$os = "apple";		
		switch($os){
			case "apple":
				$this->view->message = $this->lang->mobile->global->os->apple;
				$this->view->os = $this->lang->global->foot->tools->iphone;				
				$this->view->postdate = "2010-09-30";
			break;
			case "android":
				$tmpdownload =  Better_Download::getPhoneByOid(8);
				$this->view->message = str_replace('{URL}',$tmpdownload,$this->lang->mobile->global->os->android);			
				$this->view->os = $this->lang->global->foot->tools->android;
				$postdate = Better_DAO_DownloadProduct::getInstance()->getPostdateByOs(8);
				$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
			break;
			case "s60":
				$s60v5download =  Better_Download::getPhoneByOid(19);
				$s60v3download =  Better_Download::getPhoneByOid(6);
				$s60v23download =  Better_Download::getPhoneByOid(5);
				$s60v22download =  Better_Download::getPhoneByOid(4);
				$s60v21download =  Better_Download::getPhoneByOid(3);
				$s60v20download =  Better_Download::getPhoneByOid(2);
		
				$this->view->message = "<a href='".$s60v5download."'>".$this->lang->tools->s60->s60v5->title."</a><br>".str_replace('{V5URL}',$s60v5download,$this->lang->tools->s60->s60v5->productlist)."<br><br>";
				$this->view->message .= "<a href='".$s60v3download."'>".$this->lang->tools->s60->s60v3->title."</a><br>".str_replace('{V3URL}',$s60v3download,$this->lang->tools->s60->s60v3->productlist)."<br><br>";
				$this->view->message .="<a href='".$s60v23download."'>".$this->lang->tools->s60->s60v2version3->title."</a><br>".str_replace('{V23URL}',$s60v23download,$this->lang->tools->s60->s60v2version3->productlist)."<br><br>";
	    		$this->view->message .="<a href='".$s60v22download."'>".$this->lang->tools->s60->s60v2version2->title."</a><br>".str_replace('{V22URL}',$s60v22download,$this->lang->tools->s60->s60v2version2->productlist)."<br><br>";
	    		$this->view->message .="<a href='".$s60v21download."'>".$this->lang->tools->s60->s60v2version1->title."</a><br>".str_replace('{V21URL}',$s60v21download,$this->lang->tools->s60->s60v2version1->productlist)."<br><br>";
	    		$this->view->message .="<a href='".$s60v20download."'>".$this->lang->tools->s60->s60v2version0->title."</a><br>".str_replace('{V20URL}',$s60v20download,$this->lang->tools->s60->s60v2version0->productlist);

				$this->view->os = $this->lang->global->foot->tools->symbiam;
				$postdate = Better_DAO_DownloadProduct::getInstance()->getPostdateByOs(19);
				$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
			break;
			case "wm":
				$tmpdownload =  Better_Download::getPhoneByOid(14);
				$this->view->message = str_replace('{URL}',$tmpdownload,$this->lang->mobile->global->os->wm);				
				$this->view->os = $this->lang->global->foot->tools->wm;
				$postdate = Better_DAO_DownloadProduct::getInstance()->getPostdateByOs(14);
				$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
			break;
			case "bb":
				$tmpdownload =   str_replace('zip','jad',Better_Download::getPhoneByOid(20));
				$this->view->message = str_replace('{URL}',$tmpdownload,$this->lang->mobile->global->os->bb);
				$this->view->os = $this->lang->global->foot->tools->bb;
				$postdate = Better_DAO_DownloadProduct::getInstance()->getPostdateByOs(20);
				$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
			break;
			
		}
		$this->view->menuos = "<a href=\"/polo/tools\">".$this->lang->global->index->choice_os."</a>";
		$this->view->menubrand = "<a href=\"/polo/tools/brand\">".$this->lang->global->index->choice_brand."</a>";				
	}
	public function getproductAction()
	{
		$pid = (int)$_GET['pid'];
		$this->view->menuos = "<a href=\"/polo/tools?cat=os\">".$this->lang->global->index->choice_os."</a>";		
		$this->view->menubrand = $this->lang->global->index->choice_brand;
		$phone = Better_DAO_DownloadPhone::getInstance()->getAll(array(
			'pid' => intval($pid),
			));
		
		$brand = Better_Download::getOneBrand($phone[0]['bid']);		
		$this->view->pagemenu = "<a href=\"/polo/tools/brand\">".$this->lang->global->index->choice_brand."</a>-><a href=\"/polo/tools/phone?bid=".$brand['bid']."\">".$brand['desc']."</a>->".$phone[0]['desc'];		
		$product = Better_Download::getProductByPhoneId($pid);
		if ( count($product) ) {
			$desc = $this->lang->tools->client->version->description.":".htmlspecialchars($product[0]['desc']);
			$version = $this->lang->tools->client->version->versionno.":".htmlspecialchars($product[0]['version']);
			$date = $this->lang->tools->client->version->publishdate.":".Better_Functions::date("Y-m-d",$product[0]['postdate']);
			$link = Better_Download::getProductLinkByPhoneId($pid);
			$link = '/files/clients' . $link;
			if($phone[0]['oid']==20){
				$link = str_replace('zip','jad',$link);
			}
			$link = "<a href=".$link.">".$this->lang->global->foot->tools->title."</a>";
			//zend_debug::dump($link);
			$text = <<<EOT
 $desc	<br/>			
 $date	<br/>		
 $version	 <br/>
 $link
EOT;
		} else {
			if($phone[0]['bid']==26){
				$text = $this->lang->mobile->global->os->apple;
			}else{
				$text = $this->lang->tools->client->version->nothing;
			}	
		}
		$this->view->message = $text;		
		
		
	}		
	
	public function kaiAction() {
			
			
	}
}

