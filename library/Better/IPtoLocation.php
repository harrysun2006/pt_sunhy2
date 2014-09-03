<?php

/**
 * 根据IP查询地址相关
 *
 */
class Better_IPtoLocation
{
	
	var $StartIP = 0;
	var $EndIP = 0;
	var $Country = '';
	var $Local = '';
	
	var $CountryFlag = 0; // 标识 Country位置
	// 0×01,随后3字节为Country偏移,没有Local
	// 0×02,随后3字节为Country偏移，接着是Local
	// 其他,Country,Local,Local有类似的压缩。可能多重引用。
	var $fp;
	
	var $FirstStartIp = 0;
	var $LastStartIp = 0;
	var $EndIpOff = 0;
	
	public static function getInstance($qqwry_root_path){
		!defined('QQWRY') && define('QQWRY' , $qqwry_root_path . 'QQWry.Dat' );
		return new self();
	}
	
	function IpToInt($Ip) {
	$array=explode('.',$Ip);
	$Int=($array[0] * 256*256*256) + ($array[1]*256*256) + ($array[2]*256) + $array[3];
	return $Int;
	}
	
	
	function getStartIp ($RecNo) {
	$offset = $this->FirstStartIp + $RecNo * 7;
	@fseek ( $this->fp , $offset , SEEK_SET );
	$buf = fread ( $this->fp , 7 );
	$this->EndIpOff = ord($buf[4]) + (ord($buf[5])*256) + (ord($buf[6])* 256*256);
	$this->StartIp = ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])*256*256) + (ord($buf[3])*256*256*256);
	return $this->StartIp;
	}
	
	function getEndIp () {
	@fseek ( $this->fp , $this->EndIpOff , SEEK_SET );
	$buf = fread ( $this->fp , 5 );
	$this->EndIp = ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])*256*256) + (ord($buf[3])*256*256*256);
	$this->CountryFlag = ord ( $buf[4] );
	return $this->EndIp;
	}
	
	function getCountry () {
	
	switch ( $this->CountryFlag ) {
	case 1:
	case 2:
	$this->Country = $this->getFlagStr ( $this->EndIpOff+4);
	$this->Local = ( 1 == $this->CountryFlag )? '' : $this->getFlagStr ( $this->EndIpOff+8);
	break;
	default :
	$this->Country = $this->getFlagStr ($this->EndIpOff+4);
	$this->Local = $this->getFlagStr ( ftell ( $this->fp ));
	
		}
	}
	
	function getFlagStr ($offset)
	{
	
	$flag = 0;
	while (1){
	@fseek ( $this->fp , $offset , SEEK_SET );
	$flag = ord ( fgetc ( $this->fp ) );
	if ( $flag == 1 || $flag == 2 ) {
	$buf = fread ($this->fp , 3 );
	if ($flag == 2 ){
	$this->CountryFlag = 2;
	$this->EndIpOff = $offset - 4;
	}
	$offset = ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])* 256*256);
	}else{
	break;
	}
	
	}
	if ($offset < 12)
	return '';
	@fseek($this->fp , $offset , SEEK_SET );
	return $this->getStr();
	}
	
	
	function getStr()
	{
	$str = '';
	while (1) {
	$c = fgetc ( $this->fp );
	if ( ord ( $c[0] ) == 0 )
	break;
	$str .= $c;
	}
	return $str;
	}
	
	
	function qqwry($dotip) {
	
	$nRet='';
	$ip = $this->IpToInt($dotip);
	
	$this->fp= @fopen(QQWRY, 'rb');
	if ($this->fp == NULL) {
		$szLocal= 'OpenFileError';
	return 1;
	}
	
	@fseek ( $this->fp , 0 , SEEK_SET );
	$buf = fread ( $this->fp , 8 );
	$this->FirstStartIp = ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])*256*256) + (ord($buf[3])*256*256*256);
	$this->LastStartIp = ord($buf[4]) + (ord($buf[5])*256) + (ord($buf[6])*256*256) + (ord($buf[7])*256*256*256);
	
	$RecordCount= floor(( $this->LastStartIp - $this->FirstStartIp ) / 7);
	if ($RecordCount <= 1){
	$this->Country = 'FileDataError';
	fclose ( $this->fp );
	return 2;
	}
	
	$RangB= 0;
	$RangE= $RecordCount;
	// Match …
	while ($RangB < $RangE-1)
	{
	$RecNo= floor(($RangB + $RangE) / 2);
	$this->getStartIp($RecNo);
	
	if ($ip == $this->StartIp)
	{
	$RangB = $RecNo;
	break;
	}
	if ($ip > $this->StartIp)
	$RangB= $RecNo;
	else
	$RangE= $RecNo;
	}
	
	$this->getStartIp ($RangB);
	$this->getEndIp ();
	
	if (($this->StartIp <= $ip) && ($this->EndIp >= $ip )){
	$nRet = 0;
	$this->getCountry();
	//这样不太好…………..所以……….
	//$this->Local = str_replace('', '', $this->Local);
	//$this->Local = str_replace('CZ88.NET', '', $this->Local);
	
	}else {
	$nRet = 3;
	$this->Country = '未知';
	$this->Local = '';
	//$this->Local = str_replace(”CZ88.NET”, “”, $this->Local);
	}
	
	fclose ( $this->fp );
	return $nRet;
	}

	function ip2location($ip)
	{
		$nRet = $this->qqwry($ip); 
		//可以利用 $nRet做一些事情，我是让他自动记录未知IP到一个表,代码就不写了。
		return iconv("gb2312","utf-8",$this->Country).iconv("gb2312","utf-8",$this->Local);
	}

	function ip2city($ip)
	{
		$nRet = $this->qqwry($ip); 
		$city = iconv("gb2312","utf-8",$this->Country);
		if ($city=='局域网') {
			$city = '江苏省苏州市';
		}
		
		return $city;
	}

}