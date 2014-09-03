<?php
/**
 * Created on 2009-09-29
 *
 * @package    QBS
 * @subpackage Util
 * @author     FengJun <fengj@peptalk.cn>
 */

class Better_Service_Qbs
{
	public $server	=	'10.35.254.251';
	public $port	=	'3333';
	public $conn;
	public $socket;
	public $errno;
	public $errstr;
	public $timeout	=	1;
	public $output;
	
	protected static $instance = null;
	
	private function  __construct()
	{
		$config = Better_Config::getAppConfig()->qbs;
		$this->server = $config->server;
		$this->port = $config->port;
		$this->timeout = $config->timeout;
		
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);		
		return $this->connect();
	}

	function __destruct()
	{
		if ($this->conn) {
			socket_close($this->socket);
		}
	}
	
	public function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function connect()
	{
		$this->conn = socket_connect($this->socket, $this->server, $this->port);

		if (!$this->conn) {
			return false;
		}
		return true;
	}
	
	public function send($str)
	{
		$str = $this->str_replace_plus($str);
		socket_write($this->socket, $str, strlen($str));
		$output = $bu = '';
		while ($bu = socket_read($this->socket, 2048)) {
			$output .= $bu;
			$check_ok = $this->checkXML($output);
			if ($check_ok) {
				break;
			}
		}
		
		$this->output = $output;
	}
	
	function createTable($create_table='')
	{
		if (!$create_table) return false;
//		$id = md5($create_table);
//		$create_table = <<<EOT
//<qbs version='1.0'>
//	<drop name='bedo_user' />
//</qbs>
//EOT;
		$this->send($create_table);
		$s = $this->getQBSReturn($this->output,'create');
		if (!$s[0]) {
			$error = $s[1];
			$this->errstr = "create table error: $error\n";
			return false;
		}
		return true;
	}

	//得到创建表的状态
	function getQBSReturn($string,$mode)
	{
		$xml = new DOMDocument("1.0");
		$xml_check = @$xml->loadXML($string);
		$create = $xml->getElementsByTagName($mode)->item(0);

		try {
			if (is_object($create)) {
				$ret = $create->getAttribute('ret');
				if ('ok'==$ret){
					$s[] = true;
				} else {
					$error = $create->nodeValue;
					$s[] = false;
					$s[] = $error;
				}
			} else {
				$s[] = false;
			}
		} catch (Exception $e) {
			$s[] = false;
			Better_Log::getInstance()->logEmerg('QBS Error:['.$e.']', 'qbs');	
		}
		
		return $s;
	}
	
	function str_replace_plus($str)
	{
		$find = array("\r","\n","\t");
		$str = str_replace($find,'',$str);
		return $str;
	}
	
	function checkXML($str)
	{
		$xml = new DOMDocument("1.0");
		$xml_check = @$xml->loadXML($str);
		if ($xml_check){
			return true;
		}
		return false;
	}
	
	function updateUserXY($lon,$lat,$lbs_report,$uid,$address,$city)
	{
		$address = htmlspecialchars($address,ENT_QUOTES,'UTF-8');
		$city = htmlspecialchars($city,ENT_QUOTES,'UTF-8');
		
		list($x,$y) = Better_Functions::LL2XY($lon,$lat);
		$x = (int)$x; $y = (int)$y;
		$lbs_report = (int)$lbs_report;
		$uid = (int)$uid;
		$xml = "
<qbs version='1.0'>
	<replace name='m_user_1' id='$lbs_report' ans='1'>
		<key>
			<field name='uid'>$uid</field>
		</key>
		<x name='x'>$x</x>
		<y name='y'>$y</y>
		<order>
			<field name='lbs_report'>$lbs_report</field>
		</order>
		<fields>
			<field name='address'>$address</field>
			<field name='city'>$city</field>
		</fields>
	</replace>
</qbs>
";
		$this->send($xml);
		$s = $this->getQBSReturn($this->output,'replace');
		return $s;
	}
	
	//删除QBS中用户位置
	function deleteUserXY($uid,$lbs_report)
	{
		$uid = (int)$uid;
		$xml = <<<EOT
<qbs version='1.0'>                       
	<delete name='m_user_1' id='$lbs_report' ans='1'>
		<key>
			<field name='uid'>$uid</field>
		</key>
	</delete>
</qbs>
EOT;
		$this->send($xml);
		$s = $this->getQBSReturn($this->output,'delete');
		return $s;
	}
	
	//更新QBS中的消息的位置
	function updateBlogXY($lon,$lat,$postdate,$id,$address,$city)
	{
		$address = htmlspecialchars($address,ENT_QUOTES,'UTF-8');
		$city = htmlspecialchars($city,ENT_QUOTES,'UTF-8');
		
		list($x,$y) = Better_Functions::LL2XY($lon,$lat);
		$x = (int)$x;
		$y = (int)$y;
		$postdate = (int)$postdate;
		$xml = "
<qbs version='1.0'>
	<replace name='m_blog_1' id='$postdate' ans='1'>
		<key>
			<field name='id'>$id</field>
		</key>
		<x name='x'>$x</x>
		<y name='y'>$y</y>
		<order>
			<field name='postdate'>$postdate</field>
		</order>
		<fields>
			<field name='address'>$address</field>
			<field name='city'>$city</field>
		</fields>
	</replace>
</qbs>
";
		$this->send($xml);
		$s = $this->getQBSReturn($this->output,'replace');
		return $s;
	}

	//删除QBS中用户位置
	function deleteBlogXY($id,$postdate)
	{
		$xml = <<<EOT
<qbs version='1.0'>                       
	<delete name='m_blog_1' id='$postdate' ans='1'>
		<key>
			<field name='id'>$id</field>
		</key>
	</delete>
</qbs>
EOT;
		$this->send($xml);
		$s = $this->getQBSReturn($this->output,'delete');
		return $s;
	}	
	
	function getUserByXY($lon,$lat,$w,$h,$begin,$end)
	{
		list($x,$y) = Better_Functions::LL2XY($lon,$lat);
		$x = (int)$x;
		$y = (int)$y;
		$w = (int)$w;
		$h = (int)$h;
		$begin = (int)$begin;
		$end = (int)$end;
		$x1 = $x - $w/2;	$y1 = $y - $h/2;
		$x2 = $x + $w/2;	$y2 = $y + $h/2;

		$query = <<<EOT
<qbs version='1.0'>
	<select name='m_user_1' id='123' format='normal' order='desc'>
		<fields>
			<field name='uid' />
			<field name='lbs_report' />
		</fields>
		<where>
			<rectangle x1='$x1' y1='$y1' x2='$x2' y2='$y2'/>
		</where>
		<limit start='$begin' end='$end'/>
	</select>
</qbs>
EOT;
		$this->send($query);
		$input = $this->output;
		
		$new_dom = new DOMDocument('1.0', 'utf-8');
		$new_dom->loadXML($input);
		$select = $new_dom->getElementsByTagName('select')->item(0);
		$ret = $select->getAttribute('ret');
		
		$uids = array();
		foreach ($select->getElementsByTagName('results') as $key=>$results){
			foreach ($results->getElementsByTagName('result') as $result ){
				$jid = $lbs_report = '';
				foreach ($result->getElementsByTagName('field') as $field){
					$name = $field->getAttribute('name');
					if ($name=='uid') {
						$uid = $field->nodeValue;
					}
					if ($name=='lbs_report') {
						$lbs_report = $field->nodeValue;
					}
				}
				if ($uid && $lbs_report) {
					$uids[$uid] = $lbs_report;
				}
			}
		}
		return $uids;
	}
	
	function getBlogByXY($lon,$lat,$w,$h,$begin,$end)
	{
		list($x,$y) = Better_Functions::LL2XY($lon,$lat);
		$x = (int)$x;
		$y = (int)$y;
		$w = (int)$w;
		$h = (int)$h;
		$begin = (int)$begin;
		$end = (int)$end;
		$x1 = $x - $w/2;	$y1 = $y - $h/2;
		$x2 = $x + $w/2;	$y2 = $y + $h/2;

		$query = <<<EOT
<qbs version='1.0'>
	<select name='m_blog_1' id='456' format='normal' order='desc'>
		<fields>
			<field name='id' />
			<field name='postdate' />
		</fields>
		<where>
			<rectangle x1='$x1' y1='$y1' x2='$x2' y2='$y2'/>
		</where>
		<limit start='$begin' end='$end'/>
	</select>
</qbs>
EOT;
		$this->send($query);
		$input = $this->output;
		
		$new_dom = new DOMDocument('1.0', 'utf-8');
		$new_dom->loadXML($input);
		$select = $new_dom->getElementsByTagName('select')->item(0);
		$ret = $select->getAttribute('ret');
		
		$ids = array();
		foreach ($select->getElementsByTagName('results') as $key=>$results){
			foreach ($results->getElementsByTagName('result') as $result ){
				$jid = $lbs_report = '';
				foreach ($result->getElementsByTagName('field') as $field){
					$name = $field->getAttribute('name');
					if ($name=='id') {
						$id = $field->nodeValue;
					}
					if ($name=='postdate') {
						$postdate = $field->nodeValue;
					}
				}
				if ($id && $postdate) {
					$ids[$id] = $postdate;
				}
			}
		}

		return $ids;
	}
	
	function ___log($str)
	{
		Better_Log::getInstance()->logInfo($str, 'qbs_service');
	}
	
}


/*
QBS中用户地理位置表
<qbs version='1.0'>
	<create name='m_user_1' id='123'>
		<key>
			<field name='uid' type='char' maxsize='30'/>
		</key>
		<x name='x' min='-33554432' max='33554432'/>
		<y name='y' min='-25320479' max='25320479'/>
		<order>
			<field name='lbs_report' type='int'/>
		</order>
		<fields>
			<field name='lbs_report' type='int' />
			<field name='address' type='char' maxsize='255' />
			<field name='city' type='char' maxsize='255' />
		</fields>
	</create>
</qbs>


QBS中消息地理位置表
<qbs version='1.0'>
	<create name='m_blog_1' id='456'>
		<key>
			<field name='id' type='char' maxsize='30'/>
		</key>
		<x name='x' min='-33554432' max='33554432'/>
		<y name='y' min='-25320479' max='25320479'/>
		<order>
			<field name='postdate' type='int'/>
		</order>
		<fields>
			<field name='postdate' type='int' />
			<field name='address' type='char' maxsize='255' />
			<field name='city' type='char' maxsize='255' />
		</fields>
	</create>
</qbs>

*/


