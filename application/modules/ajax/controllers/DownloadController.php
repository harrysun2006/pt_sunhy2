<?php

/**
 * 客户端下载
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_DownloadController extends Better_Controller_Ajax
{
	protected $output = '';
	
	public function init()
	{
		parent::init(false);	
	}	

	public function initnokiaAction(){
		$brand = Better_Download::getOneBrand(1);

		$select = '<select style="width:84px;" name="brand" id="brand" onchange="getPhoneOnchange(this)">' . "\r\n";
		$option = '';

			$id = $brand['bid'];
			$desc = Better_Registry::get('language')=='en' ? $brand['name'] : $brand['desc'];
			$img = $brand['img'];
			$option .= "<option  value='$id' >$desc</option>\r\n";

		$select .= "$option";
		$select .= "</select>";
		
		$this->output = $select;		
		
		$this->outputHtml();
	}

	public function initAction()
	{
		$brands = Better_Download::getAllBrand();
		$select = '<select name="brand" id="brand" onchange="getPhoneOnchange(this)">' . "\r\n";
		$option = '';
		foreach ($brands as $v) {
			$id = $v['bid'];
			$desc = Better_Registry::get('language')=='en' ? $v['name'] : $v['desc'];
			$img = $v['img'];
			$option .= "<option  value='$id' >$desc</option>\r\n";
		}
		$select .= "$option";
		$select .= "</select>";
		
		$s = $select;
		$this->output = $select;		
		
		$this->outputHtml();
	}
	
	public function getphoneAction()
	{
		$bid = (int)$_POST['bid'];
		$phones = Better_Download::getPhoneByBid($bid);
		$select = '<select name="phone" id="phone" onchange="setPhoneImgOnchange(this)">' . "\r\n";
		$option = '';
		foreach ($phones as $v) {
			$id = $v['pid'];
			$name = $v['name'];
			$img = $v['img'];
			$option .= "<option  value='$id'>$name</option>\r\n";
		}
		$select .= "$option";
		$select .= "</select>";
		
		$c_phone_img = $phones[0]['img'];
		$c_phone_img = str_replace('_a','_b',$c_phone_img);
		$hidden =  "<input type='hidden' id='c_phone_img'  value='$c_phone_img'/>";
		
		$c_brand_img = Better_Download::getBrandImgByBid($bid);
		$hidden_b =  "<input type='hidden' id='c_brand_img'  value='$c_brand_img'/>";
		
		$s = $select . $hidden . $hidden_b;
		$this->output = $s;		
		
		$this->outputHtml();
	}
	
	public function getphoneimgAction()
	{
		$pid = (int)$_POST['pid'];
		$phoneimg = Better_Download::getPhoneImgByPid($pid);
		$phoneimg = str_replace('_a','_b',$phoneimg);
		$this->output = $phoneimg;	

		$this->outputHtml();
	}
	
	public function getproductAction()
	{
		$pid = (int)$_POST['pid'];
		$bid = (int)$_POST['bid'];
		$product = Better_Download::getProductByPhoneId($pid);
		if ( count($product) ) {
			$desc = $this->lang->tools->client->version->description.":".htmlspecialchars($product[0]['desc']);
			$version = $this->lang->tools->client->version->versionno.":".htmlspecialchars($product[0]['version']);
			$date = $this->lang->tools->client->version->publishdate.":".Better_Functions::date("Y-m-d",$product[0]['postdate']);
			$link = Better_Download::getProductLinkByPhoneId($pid);
			$link = 'files/clients' . $link;
			$link = "<a href=".$link." class='download_btn' >".$this->lang->global->foot->tools->title."</a>";			
			$text = <<<EOT
 $desc	<br/>			
 $date	<br/>		
 $version	 <br/>
 $link
EOT;
		} else {
			if($bid==26){
				$text = $this->lang->mobile->global->os->apple;
			}else{
				$text = $this->lang->tools->client->version->nothing;
			}
		}
		$this->output = $text;		
		
		$this->outputHtml();
	}
}