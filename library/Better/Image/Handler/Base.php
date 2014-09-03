<?php

/**
 * 图片处理程序基类
 * 
 * @package Better.Image.Handler
 * @author leip <leip@peptalk.cn>
 *
 */
abstract class Better_Image_Handler_Base
{
	protected static $map = array( 1 => 'gif', 2 => 'jpg', 3 => 'png', 4 => 'swf', 5 => 'psd', 6 => 'bmp' );
	protected $file = null;
	protected $info = array();

	abstract public function crop($x1, $y1, $tw, $th, $nw, $nh);
	
	public function __construct($file)
	{
		$this->file = $file;
		if (is_file($this->file)) {
			list($w, $h, $m) = @getimagesize($this->file);
			$this->info['width'] = $w;
			$this->info['height'] = $h;
			$this->info['mime'] = $m;
			$this->info['size'] = filesize($this->file);
			$this->info = array_merge($this->info, pathinfo($this->file));
		}
	}
	
	public function __get($key)
	{
		return isset($this->info[$key]) ? $this->info[$key] : '';
	}
	
	protected static function scaleImage($d=array())
	{
		$r = array(
			'w' => $d['w'],
			'h' => $d['h'],
			);
			
		if ( !$d['w'] || !$d['h']) {
			$r['w'] = $d['mw'];
			$r['h'] = $d['mh'];
			return $r;
		}
			
		if ($d['w']>$d['mw']) {
			$r['w'] = $d['mw'];
			$r['h'] = ceil(($d['h']*(($d['mw']*100)/$d['w']))/100);
			$d['h'] = $r['h'];
			$d['w'] = $r['w'];
		}
		
		if ($d['h']>$d['mh']) {
			$r['h'] = $d['mh'];
			$r['w'] = ceil(($d['w']*(($d['mh']*100)/$d['h']))/100);
		}
		return $r;
	}
}