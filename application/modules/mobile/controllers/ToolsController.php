<?php

/**
 * ToolsController
 * 
 * @author
 * @version 
 */

//require_once 'Zend/Controller/Action.php';

class Mobile_ToolsController extends Better_Mobile_Front {
	/**
	 * The default action - show the home page
	 */
	private static $maxActivities = 6;
	public function indexAction() {
		$ua = trim($_SERVER['HTTP_USER_AGENT']);
//		$ua = 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3';
//		$ua = 'Mozilla/5.0 (Linux; U; Android 1.6; zh-cn; Lenovo 3GW100 Build/Donut) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1';
//		$str = 'Mozilla/5.0 (Symbian/3; Series60/5.2 NokiaN8-00/011.012; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/525';
//		$ua = 'Nokia5320xm/52.0.007(SymbianOS/9.4;U;Series60/5.0;Mozilla/5.0;Profile/MIDP-2.1 Configuration/CLDC-1.1) AppleWebKit413 (KHTML,like Gecko) Safari/413 3gpp-gba';
//		$ua = 'android/9700/5.0.0.714 Profile/MIDP-2.1 Configuration CLDC-1.1 VendorID/100';
//		$ua = ' iemobile ';
//		$ua = strtolower($ua);
//		echo preg_match('/\b(iemobile|smartphone|ppc)\b/', $ua) ? 'Y' : 'N';
//		$ua = 'DPD-S900C/1.1023213 Mozilla/4.0 (compatible;MSIE 6.0;Windows CE; IEMobile 7.11)';
		if ($ua) {
			$r = Better_Download::formatUserAgent($ua);
			if ($r['pid']) {
				$this->view->menu = $this->lang->global->index->choice_model;
				$this->view->info = $r;
				
				$this->view->version = $this->lang->tools->client->version->versionno.":".htmlspecialchars($r['version']);
				$this->view->postdate = $this->lang->tools->client->version->publishdate.":".Better_Functions::date("Y-m-d",$r['postdate']);
				$link = str_replace('zip','jad',Better_Download::getPhoneByOid($r['oid']));
				switch ($r['os']) {
					case 'iphone':
					case 'ipod':
					case 'ipad':
					break;
					case 'android':
						$this->view->link = str_replace('{URL}', $link, $this->lang->mobile->global->os->android);
					break;
					case 's60':
						$this->view->link = str_replace('{URL}', $link, $this->lang->mobile->global->os->s60auto);
					break;
					case 'blackberry':
						$this->view->link = str_replace('{URL}', $link, $this->lang->mobile->global->os->bb);
					break;
					case 'wm':
						$this->view->link = str_replace('{URL}', $link, $this->lang->mobile->global->os->wm);
					break;
				}
				echo $this->view->render('tools/auto.phtml');
				exit;
			} else if ($r['os']) {
				switch ($r['os']) {
					case 'iphoneos':
						$this->_helper->getHelper('Redirector')->gotoSimple('chooseos','tools','mobile', array('os'=>'apple'));
					break;
					case 's60':
						$this->_helper->getHelper('Redirector')->gotoSimple('chooseos','tools','mobile', array('os'=>'s60'));
					break;
					case 'android':
						$this->_helper->getHelper('Redirector')->gotoSimple('chooseos','tools','mobile', array('os'=>'android'));
					break;
					case 'wm':
						$this->_helper->getHelper('Redirector')->gotoSimple('chooseos','tools','mobile', array('os'=>'wm'));
					break;
					case 'blackberry':
						$this->_helper->getHelper('Redirector')->gotoSimple('chooseos','tools','mobile', array('os'=>'bb'));
					break;
				}
			}
		}
		$this->view->menuos = $this->lang->global->index->choice_os;
		$this->view->menubrand = "<a href=\"/mobile/tools/brand\">".$this->lang->global->index->choice_brand."</a>";
		
	}
	public function brandAction() {
		$page = (int)$_GET['page'];	
		        	
       	if (!$page )
			$page = 1;		
			
		$this->view->menuos = "<a href=\"/mobile/tools/chooseos\">".$this->lang->global->index->choice_os."</a>";
		
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
		
		$this->view->menuos = "<a href=\"/mobile/tools?cat=os\">".$this->lang->global->index->choice_os."</a>";		
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
		$this->view->brandurl = "<a href=\"/mobile/tools/brand\">".$this->lang->global->index->choice_brand."</a>->".$tempbrandname;
	   if($brandid==29){	   		
	   		$this->_helper->getHelper('Redirector')->gotoUrl('/mobile/tools/chooseos?os=bb');
			exit(0);
	   }
				
		if( $total > $page * 100 )
	    	$this->view->urlNext = "<a href=\"/mobile/tools/phone?bid=".$brandid."&page=".($page + 1)."\">".$this->lang->mobile->pager->next."</a>";
	    if ( $page > 1 )
	    	$this->view->urlPrev = " <a href=\"/mobile/tools/phone?bid=".$brandid."&page=".($page - 1)."\">".$this->lang->mobile->pager->pre."</a>";	
	            
	}
	
	public function chooseosAction(){
		$os = $this->_request->getParam('os');
		$os = str_replace(';','',$os);
		$os = str_replace(':','',$os);
		if(!$os) {
			$this->view->menuos = $this->lang->global->index->choice_os;
			$this->view->menubrand = "<a href=\"/mobile/tools/brand\">".$this->lang->global->index->choice_brand."</a>";
			echo $this->view->render('tools/index.phtml');
			exit(0);
		}
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
				$this->view->message = "<a href='".$s60v5download."'>".$this->lang->tools->s60->s60s3->title."</a><br>".str_replace('{V5URL}',$s60v5download,$this->lang->tools->s60->s60s3->productlist)."<br><br>";
				$this->view->message .= "<a href='".$s60v5download."'>".$this->lang->tools->s60->s60v5->title."</a><br>".str_replace('{V5URL}',$s60v5download,$this->lang->tools->s60->s60v5->productlist)."<br><br>";
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
								
				$tmpv50download =   str_replace('zip','jad',Better_Download::getPhoneByOid(20));
				$tmpv46download =   str_replace('zip','jad',Better_Download::getPhoneByOid(22));
				$tmpv45download =   str_replace('zip','jad',Better_Download::getPhoneByOid(21));
				/*
				$tmpv50download =  Better_Download::getPhoneByOid(20);
				$tmpv46download =  Better_Download::getPhoneByOid(22);
				$tmpv45download =  Better_Download::getPhoneByOid(21);
				*/
				$this->view->message = $this->lang->tools->bb->v50->title."<br/>";
				$this->view->message .= str_replace('{V50URL}',$tmpv50download,$this->lang->tools->bb->v50->productlist);
				$this->view->message .= "<br/>".$this->lang->tools->bb->v46->title."<br/>";
				$this->view->message .= str_replace('{V46URL}',$tmpv46download,$this->lang->tools->bb->v46->productlist);
				$this->view->message .= "<br/>".$this->lang->tools->bb->v45->title."<br/>";
				$this->view->message .= str_replace('{V45URL}',$tmpv45download,$this->lang->tools->bb->v45->productlist);				
				$this->view->os = $this->lang->global->foot->tools->bb;
				$postdate = Better_DAO_DownloadProduct::getInstance()->getPostdateByOs(20);
				$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
			break;
			
		}
		$this->view->menuos = "<a href=\"/mobile/tools/chooseos\">".$this->lang->global->index->choice_os."</a>";
		$this->view->menubrand = "<a href=\"/mobile/tools/brand\">".$this->lang->global->index->choice_brand."</a>";				
	}
	public function getproductAction()
	{
		$pid = (int)$_GET['pid'];
		$this->view->menuos = "<a href=\"/mobile/tools?cat=os\">".$this->lang->global->index->choice_os."</a>";		
		$this->view->menubrand = $this->lang->global->index->choice_brand;
		$phone = Better_DAO_DownloadPhone::getInstance()->getAll(array(
			'pid' => intval($pid),
			));
		
		$brand = Better_Download::getOneBrand($phone[0]['bid']);		
		$this->view->pagemenu = "<a href=\"/mobile/tools/brand\">".$this->lang->global->index->choice_brand."</a>-><a href=\"/mobile/tools/phone?bid=".$brand['bid']."\">".$brand['desc']."</a>->".$phone[0]['desc'];		
		$product = Better_Download::getProductByPhoneId($pid);
		if ( count($product) ) {
			$desc = $this->lang->tools->client->version->description.":".htmlspecialchars($product[0]['desc']);
			$version = $this->lang->tools->client->version->versionno.":".htmlspecialchars($product[0]['version']);
			$date = $this->lang->tools->client->version->publishdate.":".Better_Functions::date("Y-m-d",$product[0]['postdate']);
			$link = Better_Download::getProductLinkByPhoneId($pid);
			$link = '/files/clients' . $link;
			if($phone[0]['oid']==20 || $phone[0]['oid']==21 || $phone[0]['oid']==22 ){
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
	public function poloAction(){
		
	}
	
	
	public function outerAction(){
		$reflink = $_GET['reflink'];		
		$this->view->reflink = $reflink;
			
		$this->view->menuos = "<a href=\"/mobile/tools/outerindex?reflink=".$reflink."\">".$this->lang->global->index->choice_os."</a>";
		
		$this->view->menubrand = $this->lang->global->index->choice_brand;
		$brands = Better_Download::getAllBrand();		
		for($i=0;$i<count($brands);$i++){
			$brands[$i]['desc'] = Better_Registry::get('language')=='en' ? $brands[$i]['name'] : $brands[$i]['desc'];
		}		
		$this->view->brands = $brands;
		$this->view->reflink = $reflink;
	}
	public function outerindexAction(){
		$reflink = $_GET['reflink'];		
		$this->view->reflink = $reflink;
		$this->view->menuos = $this->lang->global->index->choice_os;
		$this->view->menubrand = "<a href=\"/mobile/tools/outerbrand?reflink=".$reflink."\">".$this->lang->global->index->choice_brand."</a>";	
	}
	public function outerchooseosAction(){
		$reflink = $_GET['reflink'];	
		$os = $_GET['os'];	
		$os = str_replace(';','',$os);
		$os = str_replace(':','',$os);	
		$tempnuma = "1".$reflink;
		$tempnum = (int)$tempnuma-10000;
		$filenum = str_pad($tempnum, 3, "0", STR_PAD_LEFT);	
		if(!$os)
			$os = "apple";		
		switch($os){
			case "apple":
				$this->view->message = $this->lang->mobile->global->os->apple;
				$this->view->os = $this->lang->global->foot->tools->iphone;				
				$this->view->postdate = "2010-09-30";
			break;
			case "android":
				//$tmpdownload =  Better_Download::getPhoneByOid(8);
				
				$tmpdownload = "http://k.ai/files/out_clients/".$reflink."/kai_14_".$filenum.".apk";				
				$this->view->message = str_replace('{URL}',$tmpdownload,$this->lang->mobile->global->os->android);			
				$this->view->os = $this->lang->global->foot->tools->android;
				$postdate = Better_DAO_DownloadProduct::getInstance()->getPostdateByOs(8);
				$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
			break;
			case "s60":
				/*
				$s60v5download =  Better_Download::getPhoneByOid(19);
				$s60v3download =  Better_Download::getPhoneByOid(6);
				$s60v23download =  Better_Download::getPhoneByOid(5);
				$s60v22download =  Better_Download::getPhoneByOid(4);
				$s60v21download =  Better_Download::getPhoneByOid(3);
				$s60v20download =  Better_Download::getPhoneByOid(2);
				*/
				
				$s60v5download =  "http://k.ai/files/out_clients/".$reflink."/kai_5th_".$filenum.".sisx";
				$s60v3download =  "http://k.ai/files/out_clients/".$reflink."/kai_3rd_".$filenum.".sisx";
				$s60v23download = "http://k.ai/files/out_clients/".$reflink."/kai_23_".$filenum.".sis";
				$s60v22download = "http://k.ai/files/out_clients/".$reflink."/kai_22_".$filenum.".sis";
				$s60v21download = "http://k.ai/files/out_clients/".$reflink."/kai_21_".$filenum.".sis";
				$s60v20download = "http://k.ai/files/out_clients/".$reflink."/kai_20_".$filenum.".sis";
				
				
				
				$this->view->message = "<a href='".$s60v5download."'>".$this->lang->tools->s60->s60s3->title."</a><br>".str_replace('{V5URL}',$s60v5download,$this->lang->tools->s60->s60s3->productlist)."<br><br>";
				$this->view->message .= "<a href='".$s60v5download."'>".$this->lang->tools->s60->s60v5->title."</a><br>".str_replace('{V5URL}',$s60v5download,$this->lang->tools->s60->s60v5->productlist)."<br><br>";
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
				$tmpdownload =  "http://k.ai/files/out_clients/".$reflink."/Kai_1.5_0".$filenum.".cab";
				$this->view->message = str_replace('{URL}',$tmpdownload,$this->lang->mobile->global->os->wm);				
				$this->view->os = $this->lang->global->foot->tools->wm;
				$postdate = Better_DAO_DownloadProduct::getInstance()->getPostdateByOs(14);
				$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
			break;
			case "bb":								
				$tmpv50download =   str_replace('zip','jad',Better_Download::getPhoneByOid(20));
				$tmpv46download =   str_replace('zip','jad',Better_Download::getPhoneByOid(22));
				$tmpv45download =   str_replace('zip','jad',Better_Download::getPhoneByOid(21));				
				$this->view->message = $this->lang->tools->bb->v50->title."<br/>";
				$this->view->message .= str_replace('{V50URL}',$tmpv50download,$this->lang->tools->bb->v50->productlist);
				$this->view->message .= "<br/>".$this->lang->tools->bb->v46->title."<br/>";
				$this->view->message .= str_replace('{V46URL}',$tmpv46download,$this->lang->tools->bb->v46->productlist);
				$this->view->message .= "<br/>".$this->lang->tools->bb->v45->title."<br/>";
				$this->view->message .= str_replace('{V45URL}',$tmpv45download,$this->lang->tools->bb->v45->productlist);				
				$this->view->os = $this->lang->global->foot->tools->bb;
				$postdate = Better_DAO_DownloadProduct::getInstance()->getPostdateByOs(20);
				$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
			break;
			
		}
		$this->view->menuos = "<a href=\"/mobile/tools/outerindex?reflink=".$reflink."\">".$this->lang->global->index->choice_os."</a>";
		$this->view->menubrand = "<a href=\"/mobile/tools/outerbrand?reflink=".$reflink."\">".$this->lang->global->index->choice_brand."</a>";				
	}
	
	public function outerbrandAction() {
		$page = (int)$_GET['page'];	
		$reflink = $_GET['reflink'];	        	
       	if (!$page )
			$page = 1;		
			
		$this->view->menuos = "<a href=\"/mobile/tools/outerindex?reflink=".$reflink."\">".$this->lang->global->index->choice_os."</a>";
		
		$this->view->menubrand = $this->lang->global->index->choice_brand;
		$brands = Better_Download::getAllBrand();		
		for($i=0;$i<count($brands);$i++){
			$brands[$i]['desc'] = Better_Registry::get('language')=='en' ? $brands[$i]['name'] : $brands[$i]['desc'];
		}		
		$this->view->brands = $brands;
		$this->view->reflink = $reflink;
	}
	
	public function outerphoneAction() {
		$page = (int)$_GET['page'];
		$brandid = $_GET['bid'];
		$reflink = $_GET['reflink'];
		$this->view->reflink = $reflink;        	
       	if (!$page )
			$page = 1;
		if (!$brandid )
			$brandid = 1;
		
		$this->view->menuos = "<a href=\"/mobile/tools?cat=os\">".$this->lang->global->index->choice_os."</a>";		
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
		$this->view->brandurl = "<a href=\"/mobile/tools/outerbrand?reflink=".$reflink."\">".$this->lang->global->index->choice_brand."</a>->".$tempbrandname;
		if($brandid==29){	   		
	   		$this->_helper->getHelper('Redirector')->gotoUrl('/mobile/tools/chooseos?os=bb');
			exit(0);
	   }
				
		if( $total > $page * 100 )
	    	$this->view->urlNext = "<a href=\"/mobile/tools/outerphone?reflink=".$reflink."&bid=".$brandid."&page=".($page + 1)."\">".$this->lang->mobile->pager->next."</a>";
	    if ( $page > 1 )
	    	$this->view->urlPrev = " <a href=\"/mobile/tools/outerphone?reflink=".$reflink."&bid=".$brandid."&page=".($page - 1)."\">".$this->lang->mobile->pager->pre."</a>";	
	    if($brandid==26){
	    	$this->view->message = $this->lang->mobile->global->os->apple;
			$this->view->os = $this->lang->global->foot->tools->iphone;
			$this->view->brandid = $brandid;
	    }        
	}
	
	public function outergetproductAction()
	{
		$pid = (int)$_GET['pid'];
		$reflink = $_GET['reflink'];
		$tempnuma = "1".$reflink;
		$tempnum = (int)$tempnuma-10000;
		$filenum = str_pad($tempnum, 3, "0", STR_PAD_LEFT);	
		$this->view->reflink = $reflink;        	
		$this->view->menuos = "<a href=\"/mobile/tools/outerindex?reflink=".$reflink."\">".$this->lang->global->index->choice_os."</a>";		
		$this->view->menubrand = $this->lang->global->index->choice_brand;
		$phone = Better_DAO_DownloadPhone::getInstance()->getAll(array(
			'pid' => intval($pid),
			));
		
		$brand = Better_Download::getOneBrand($phone[0]['bid']);		
		$this->view->pagemenu = "<a href=\"/mobile/tools/outerbrand?reflink=".$reflink."\">".$this->lang->global->index->choice_brand."</a>-><a href=\"/mobile/tools/outerphone?reflink=".$reflink."&bid=".$brand['bid']."\">".$brand['desc']."</a>->".$phone[0]['desc'];		
		$product = Better_Download::getProductByPhoneId($pid);
		if ( count($product) ) {
			$desc = $this->lang->tools->client->version->description.":".htmlspecialchars($product[0]['desc']);
			$version = $this->lang->tools->client->version->versionno.":".htmlspecialchars($product[0]['version']);
			$date = $this->lang->tools->client->version->publishdate.":".Better_Functions::date("Y-m-d",$product[0]['postdate']);
			$link = Better_Download::getProductLinkByPhoneId($pid);
			
			
			
			$tmpdownload[19] =  "http://k.ai/files/out_clients/".$reflink."/kai_5th_".$filenum.".sisx";
			$tmpdownload[6] =  "http://k.ai/files/out_clients/".$reflink."/kai_3rd_".$filenum.".sisx";
			$tmpdownload[5] = "http://k.ai/files/out_clients/".$reflink."/kai_23_".$filenum.".sis";
			$tmpdownload[4] = "http://k.ai/files/out_clients/".$reflink."/kai_22_".$filenum.".sis";
			$tmpdownload[3] = "http://k.ai/files/out_clients/".$reflink."/kai_21_".$filenum.".sis";
			$tmpdownload[2] = "http://k.ai/files/out_clients/".$reflink."/kai_20_".$filenum.".sis";
			$tmpdownload[14] =  "http://k.ai/files/out_clients/".$reflink."/Kai_1.5_0".$filenum.".cab";
			$tmpdownload[15] =  "http://k.ai/files/out_clients/".$reflink."/Kai_1.5_0".$filenum.".cab";
			$tmpdownload[16] =  "http://k.ai/files/out_clients/".$reflink."/Kai_1.5_0".$filenum.".cab";
			$tmpdownload[17] =  "http://k.ai/files/out_clients/".$reflink."/Kai_1.5_0".$filenum.".cab";
			$tmpdownload[8] = "http://k.ai/files/out_clients/".$reflink."/kai_14_".$filenum.".apk";				
			$link = $tmpdownload[$phone[0]['oid']];
			/*
			if($phone[0]['oid']==20 || $phone[0]['oid']==21 || $phone[0]['oid']==22 ){
				$link = str_replace('zip','jad',$link);
			}
			*/
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
}

