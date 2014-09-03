<?php

/**
 * 下载
 * 
 * @package Better
 * @author fengjun <fengj@peptalk.cn>
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Download 
{

	public function __construct()
	{
	}
	
	/**
	 * 获取所有品牌
	 * 
	 * @return array
	 */
	public static function getAllBrand()
	{
		return Better_DAO_DownloadBrand::getInstance()->getAll(array(
			'order' => 'order_no ASC',
			));
	}
	
	/**
	 * 获取某一品牌
	 */
	public static function getOneBrand($bid){
		return Better_DAO_DownloadBrand::getInstance()->find($bid);
	}
	
	
	
	/**
	 * 根据品牌id获取所有手机型号
	 * 
	 * @param $bid
	 * @return array
	 */
	public static function getPhoneByBid($bid)
	{
		return Better_DAO_DownloadPhone::getInstance()->getAll(array(
			'bid' => intval($bid),
			'order' => 'BINARY(`name`) DESC',
			));
	}
	
	/**
	 * 根据品牌id获取品牌图片
	 * 
	 * @param $bid
	 * @return string
	 * 
	 */
	public static function getBrandImgByBid($bid)
	{
		$row = Better_DAO_DownloadBrand::getInstance()->get(array(
			'bid' => intval($bid),
			));
		return isset($row['img']) ? $row['img'] : '';
	}

	/**
	 * 根据手机型号id获取手机图片
	 * 
	 * @param $pid
	 * @return string
	 */
	public static function getPhoneImgByPid($pid)
	{
		$row = Better_DAO_DownloadPhone::getInstance()->get(array(
			'pid' => intval($pid),
			));
		return isset($row['img']) ? $row['img'] : '';
	}	
	
	/**
	 * 根据型号id获取产品
	 * 
	 * @param $pid
	 * @return array
	 */
	public static function getProductByPhoneId($pid)
	{
		return Better_DAO_DownloadProduct::getInstance()->getProductByPhoneId($pid);
	}
	
	public static function getProductLinkByPhoneId($pid)
	{
		$tmp = Better_DAO_DownloadProduct::getInstance()->getProductByPhoneId($pid, '1');
		$row = $tmp[0];
		
		$temp_array = explode('_',$row['os_name']);
		$dir_name = $temp_array[0];
		$c_os = $temp_array[1];
		if ($c_os) $c_os = '_' . $c_os;
		
		$version = '_' . $row['version'];
		$temp_array = explode('.',$row['filename']);
		$full_filename = $temp_array[0] . $c_os . $version . '.' . $temp_array[1];
		$link = "/$dir_name/$full_filename";
		
		return $link;
	}
	
	public static function getPhoneByBidWap($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		if (is_array($page)) {
			$params = $page;
			$params['page_size'] || $params['page_size'] = $pageSize;
			$params['bid'] = $page['bid'];			
		} else {
			$params = array(
				'page' => $page,
				'page_size' => $pageSize,
				);
		}			
		$return = array(
			'count' => 0,
			'rows' => array(),	
			);	
		$total=  Better_DAO_DownloadPhone::getInstance()->getPhoneByBidCountWap($params); 
		return array(
				'count' =>(int)$total['total'],
				'rows' => Better_DAO_DownloadPhone::getInstance()->getPhoneByBidWap($params),		
		);
	}

	/**
	 * 获取操作系统的下载链接
	 * 
	 * @param $bid
	 * @return array
	 */
	public static function getPhoneByOid($oid)
	{
		$date =  Better_DAO_DownloadPhone::getInstance()->getPhoneByOid(array(			
			'oid' => intval($oid),
			'limit' => 1,
			));	
		$tmppid = isset($date['pid'])?	$date['pid']: 10;
		$link = Better_Download::getProductLinkByPhoneId($tmppid);
		$link = '/files/clients' . $link;
		return $link;
	}
	
	/**
	 * 格式化 user-agent
	 * @param 	$ua
	 * @return 	array
	 */
	public static function formatUserAgent($useragent)
	{
		$ua = strtolower($useragent);
		if ($ua == null) return;
		$sPos = strpos($ua, '(');
		$ePos = strpos($ua, ')');
		$ueCroe = substr($ua, $sPos + 1, $ePos - $sPos - 1);
		$data = explode(';', $ueCroe);
		$r = array();
		if (preg_match('/\b(iphone|ipod|ipad)\b/', $ua)) {
			return array('os'=>'iphoneos');
		}
		/*if (preg_match('/\biphone\b/', $ua)) {//iPhone
			$os = 'iphoneos';
			$iArr = explode(' ', $data['2']);
			$iVersion = substr($iArr['3'], 0, 1);
			switch ($iVersion) {
				case '4' : 
					$model = 'iphone 4';
				break;
				case '3' : 
					$model = 'iphone 3g';
				break;
				case '4' : 
					$r['pid'] = 692;
				break;
				case '4' : 
					$r['pid'] = 692;
				break;
			}
			
		} else if (preg_match('/\bipod\b/', $ua)) {//iPod
			$os = 'iphoneos';
			$model = 'ipod';
		} else if (preg_match('/\bipad\b/', $ua)) {//iPad
			$os = 'iphoneos';
//			$iArr = explode(' ', $data['2']);
//			$iVersion = substr($iArr['3'], 0, 1);
			$model = 'ipad';
		}*/
		//N8:Mozilla/5.0 (Symbian/3; Series60/5.2 NokiaN8-00/011.012; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/525
		//5800W:Nokia5800w/52.0.007(SymbianOS/9.4;U;Series60/5.0;Mozilla/5.0;Profile/MIDP-2.1 Configuration/CLDC-1.1) AppleWebKit413 (KHTML,like Gecko) Safari/413 3gpp-gba
		//6681:Nokia6681/2.0 (6.09.00) SymbianOS/8.0 Series60/2.6 Profile/MIDP-2.0 Configuration/CLDC-1.1 S60V2 2
		//N72:NokiaN72/2.0625.2.0.2 Series60/2.8 Profile/MIDP-2.0 Configuration/CLDC-1.1 S60V2 3
		
		if (preg_match('/\b(series60|symbian os series 60|symbianos\/\S+ series60)\b/', $ua)) {//S60
			$os = 's60';
		}
		
		preg_match('/\b(nokia\w{0,}[-a-zA-Z0-9]{2,}|nokia\w{0,})\b/', $ua, $matches);
		if ($matches) {
			$model = str_replace('nokia', '', $matches['1']);
		} else {
			preg_match('/\b(sonyericsson\w{0,}[-a-zA-Z0-9]{2,}|sonyericsson\w{0,})\b/', $ua, $matches);
			$model = str_replace('sonyericsson', '', $matches['1']);
		}
		//M9:Mozilla/5.0 (Linux; U; Android 2.2; zh-cn; meizu_m9 Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
		//MB525:Mozilla/5.0 (Linux; U; Android 2.1-update1;zh-cn;MB525 Build/JRDNEM_U3_2.59.0) AppleWebKit/530.17(KHTML, like Gecko) Version/4.0 Mobile Safari/530.17
		//3GW100:Mozilla/5.0 (Linux; U; Android 1.6; zh-cn; Lenovo 3GW100 Build/Donut) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1
		if (!$model && preg_match('/\bandroid\b/', $ua)) {//Android
			$os = 'android';
			if ($data['4']) {
				$modelArr = explode(' ', $data['4']);
				$bPos = strpos($data['4'], 'build');
				if ($bPos === false) {
					$model = $data['4'];
				} else {
					$model = substr($data['4'], 0, $bPos);
				}
			}
		}
		//BlackBerry/9700/5.0.0.714 Profile/MIDP-2.1 Configuration CLDC-1.1 VendorID/100
		if (!$model && preg_match('/\b(blackberry)\b/', $ua)) {
			$os = 'blackberry';
			$uaArr = explode(' ', $ua);
			$data = explode('/', $uaArr['0']);
			$model = $data['1'];
		}
		//PPC:Mozilla/4.0 (compatible;MSIE 6.0;Windows CE; IEMobile 7.11)
		if (!$model && preg_match('/\b(iemobile|smartphone|ppc|windows ce)\b/', $ua)) {
			$os = 'wm';
		}
		preg_match('/\bdpd-\w{0,}\b/', $ua, $matches);
		if ($matches) {
			$model = str_replace('dpd-', '', $matches['0']);
		}
		
		if (!$os) {
			if ($data['0'] == 'linux' && $data['1'] == 'u' && $data['4']) {
				$bPos = strpos($data['4'], 'build');
				if ($bPos === false) {
					$model = $data['4'];
				} else {
					$model = substr($data['4'], 0, $bPos);
				}
			}
		}
		if ($model) {
			$info = Better_DAO_DownloadPhone::getInstance()->getPhoneByName($model);
			$info = count($info) > 0 ? $info['0'] : '';
		}
		if ($info) {
			switch ($info['oname']) {
				case 'Android':
					$os = 'android';
				break;
				case 'S60_20':
				case 'S60_21':
				case 'S60_22':
				case 'S60_23':
				case 'S60_30':
				case 'S60_50':
					$os = 's60';
				break;
				case 'WindowsMobile_5.0':
				case 'WindowsMobile_6.0':
				case 'WindowsMobile_6.1':
				case 'WindowsMobile_6.5':
					$os = 'wm';
				break;
				case 'Blackberry':
					$os = 'blackberry';
				break;
			}
			$info['os'] = $os;
			return $info;
		} else {
			Better_Log::getInstance()->log($model . "\t" . $os . "\t" . $useragent, 'mobileDownloadUseragent', false);
			return array('model'=>$model, 'os'=>$os);
		}
	}
}

?>