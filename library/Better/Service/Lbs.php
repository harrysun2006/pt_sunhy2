<?php
/**
 * Created on 2009-09-28
 *
 * @package    LBS
 * @subpackage Util
 * @author     FengJun <fengj@peptalk.cn>
 */

class Better_Service_Lbs
{
	public	$host	=	'http://www.lbs.org.cn';
	public	$port	=	80;
	public	$url	=	'/api/lbs/location_service.php?async=1';
	public	$config	=	array('maxredirects' => 0,'timeout'=> 5);
	public	$client;
	public	$response;
	public	$body;
	
	public $error	=	false;
	public $message	=	'';
	public $lat 	=	-1;
	public $lon 	=	-1;
	public $x		=	0;
	public $y		=	0;
	public $time 	=	0;
	public $range 	=	0;
	
	protected static $instance = null;
	
	private function __construct()
	{
		$this->host = Better_Config::getAppConfig()->lbs->server->url;
		$host	=	$this->host;
		$url	=	$this->url;
		$this->client = new Zend_Http_Client($host.$url, $this->config);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function send($body)
	{
		$this->client->setRawData($body)->setEncType('text/xml');
		$this->response = $this->client->request('POST');

		if (!$this->response->isSuccessful()) {
			$this->error($this->response->getStatus());
			
			return false;
		}
		return true;
	}
	
	public function parseXML($str)
	{
		$xml = new DOMDocument(1.0);
		$check_xml = @$xml->loadXML($str);
		if (!$check_xml) {
			$this->error($str);
			return false;
		}
		
		$location = $xml->getElementsByTagName('location')->item(0);
		$lat = $location->getAttribute('lat');
		$lon = $location->getAttribute('lon');
		$time = $location->getAttribute('time');
		$range = $location->getAttribute('range');
		if ($lon && $lat) {
			$this->lat = $lat;
			$this->lon = $lon;
			$this->time = $time;
			$this->range = $range;
			list($this->x,$this->y) = Better_Functions::LL2XY($this->lon,$this->lat);
		} else {
			$this->error($str);
			return false;
		}
	}
	
	public function getLL($body, $uid=0, $cache=true)
	{
		try {
			$begin_time = microtime(true);
			$is_ok = $this->send($body);
			$str = $this->response->getBody();
			$end_time = microtime(true);
			
			$exec_time = $end_time - $begin_time;
			$_strlog = "LBS\t$exec_time\r\n";
			Better_Functions::sLog($_strlog, 'exec_time.log');
			Better_Registry::set('LBS', $exec_time);
			
			Better_Log::getInstance()->logInfo($body, 'lbs_body');
			Better_Log::getInstance()->logInfo($str, 'lbs_response');
			
			$rLbs = Better_Registry::get('lbs_last');
			$rLbs['body'] = $body;
			$rLbs['response'] = $str;
			Better_Registry::set('lbs_last', $rLbs);
			
			
			if (!$is_ok) {
				$this->error($str);
				return false;
			}
			$this->parseXML($str);
			
			if ($uid && $this->lon && $this->lat && $this->lon<200 && $this->lat<200 && $this->lon>-200 && $this->lat>-200) {
				if ($cache) {
					$this->cacheLastResult($uid);
				}
			} else if ($uid) {
				$cached = Better_DAO_Lbs_Cache::getInstance()->getUid($uid);
				if ($cached['lon'] && $cached['lat']) {
					$this->lon = $cached['lon'];
					$this->lat = $cached['lat'];
					$this->time = $cached['time'];
					$this->range = $cached['range'];
					$this->x = $cached['x'];
					$this->y = $cached['y'];
				}
			}
		} catch (Exception $e) {
			$str = '';
			 if ($uid) {
				$cached = Better_DAO_Lbs_Cache::getInstance()->getUid($uid);
				if ($cached['lon'] && $cached['lat']) {
					$this->lon = $cached['lon'];
					$this->lat = $cached['lat'];
					$this->time = $cached['time'];
					$this->range = $cached['range'];
					$this->x = $cached['x'];
					$this->y = $cached['y'];
				}
			}			
			
			Better_Log::getInstance()->logInfo($e->getTraceAsString(), 'lbs_exception');
		}
		
		return $str;
	}
	
	/**
	 * 缓存上一次结果
	 * 
	 * @return null
	 */
	public function cacheLastResult($uid=0)
	{
		Better_DAO_Lbs_Cache::getInstance()->replace(array(
			'uid' => $uid,
			'lon' => $this->lon,
			'lat' => $this->lat,
			'time' => $this->time,
			'range' => $this->range,
			'x' => $this->x,
			'y' => $this->y,
			'cache_time' => time()		
			));
	}
	
	public function getLLbyIp($uid, $ip='')
	{
		$ip || $ip = Better_Functions::getIP();
		$uid = intval($uid);
		
		$xml = "<location ver='0.1' vendid='".Better_Config::getAppConfig()->lbs->api_key."' os='win' from='better' id='".$uid."'><locate  data='BBADAwAjiU+fIL4bAhy/AAEisggA7gcCCufFBAIM8T3H1ccA=='></locate><ip>".$ip."</ip><urls html='1' wml='1'/></location>";
		
		$is_ok = $this->send($xml);
		$str = $this->response->getBody();
		if (!$is_ok) {
			$this->error($str);
			return false;
		}
		$this->parseXML($str);
		return $str;
	}

	public static function getDistance($lon1,$lat1,$lon2,$lat2)
	{
		$PI = pi();
		$R = 6.3781e6 ;
		$x = ($lon2-$lon1)*$PI*$R*cos( (($lat1+$lat2)/2) *$PI/180)/180;
		$y = ($lat2-$lat1)*$PI*$R/180;
		$out = hypot($x,$y);
		return $out;
	}
	
	function error($str)
	{
		$this->error = true;
		$this->message = $str;
		
		return false;
	}
	

	
}
