<?php

/**
 * 图片处理
 *
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Image
{
	
	protected static $map = array( 1 => 'gif', 2 => 'jpg', 3 => 'png', 4 => 'swf', 5 => 'psd', 6 => 'bmp' );
	
	/**
	 * 裁剪图片
	 *
	 * @param string $file 文件位置
	 * @param integer $x1 起点x坐标
	 * @param integer $y1 起点y坐标
	 * @param integer $x2 终点x坐标
	 * @param integer $y2 终点y坐标
	 * @return unknown_type
	 */
	public static function crop($file, $x1, $y1, $tw, $th, $nw, $nh)
	{
		$newFile = '';
		$newFile = $file;
		
		list($w, $h, $m) = getimagesize($file);
		$dir = dirname($file).'/';
		$fileName = basename($file);
		
		switch(self::$map[$m]) {
			case 'gif':
				$image = imagecreatefromgif($file);
				break;
			case 'jpg':
				$image = imagecreatefromjpeg($file);
				break;
			case 'png':
				$image = imagecreatefrompng($file);
				break;
		}
		
		if ($image) {
			$ni = imagecreatetruecolor($nw, $nh);
			//bool imagecopyresampled
			// ( resource $dst_image  , resource $src_image  , int $dst_x  , int $dst_y  , int $src_x  , int $src_y  ,
			// int $dst_w  , int $dst_h  , int $src_w  , int $src_h  )
			imagecopyresampled($ni, $image, 0, 0, $x1, $y1, $nw, $nh, $tw, $th);
			
			$tmp = pathinfo($fileName);
			$newFile = $dir.'crop_'.$tmp['filename'].'.jpg';
								
			imagejpeg($ni, $newFile);
			chmod($newFile, 0777);
			imagedestroy($ni);
			imagedestroy($image);
		}
		
		return $newFile;
	}

	/**
	 * 生成图片缩略图
	 *
	 * @param $file
	 * @param $mw
	 * @param $mh
	 * @return string
	 */
	public static function genThumb($file, $mw=400, $mh=300, $prefix='thumb_')
	{
		list($w, $h, $m) = getimagesize($file);
		$thumbFile = '';
		$dir = dirname($file).'/';
		$fileName = basename($file);
		
		$im = self::scaleImage(array(
				'w' => $w,
				'h' => $h,
				'mw' => $mw,
				'mh' => $mh,
				));
		$tw = $im['w'];
		$th = $im['h'];
		$image = null;
			
		switch(self::$map[$m]) {
			case 'gif':
				$image = imagecreatefromgif($file);
				break;
			case 'jpg':
				$image = imagecreatefromjpeg($file);
				break;
			case 'png':
				$image = imagecreatefrompng($file);
				break;
		}
		$func = 'imagejpeg';
					
		if ($w>$mw || $h>$mh) {
			
		} else {
			$tw = $w;
			$th = $h;
		}
		
		if ($image) {
			$thumb = imagecreatetruecolor($tw, $th);
			imagecopyresampled($thumb, $image, 0, 0, 0, 0, $tw, $th, $w, $h);
			if ($prefix=='') {
				unlink($file);
				$thumbFile = $file;
			} else {
				$tmp = pathinfo($fileName);
				$thumbFile = $dir.$prefix.$tmp['filename'].'.jpg';
			}
			$func($thumb, $thumbFile);
			chmod($thumbFile, 0777);
			imagedestroy($thumb);
			imagedestroy($image);
		}

		return $thumbFile;
	}
	
	/**
	 * 根据传入的最大尺寸以及图片的原始尺寸，扩展图片的大小
	 * 数组键名：
	 * mh - 允许的最大高度
	 * mw - 允许的最大宽度
	 * h - 原始高度
	 * w - 原始宽度
	 *
	 * @param $d
	 * @return array
	 */
	public static function scaleImage($d=array())
	{
		$r = array(
			'w' => $d['w'],
			'h' => $d['h'],
			);
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
	
	/**
	 * 生成一个验证码图片
	 *
	 * @param $code
	 * @param $w
	 * @param $h
	 * @return unknown_type
	 */
	public static function genSCode($code, $w=100, $h=40)
	{
		Better_Image_Handler::factory(null)->genSCode($code, $w, $h);
	}
	
}