<?php

/**
 * GD图片处理
 * 
 * @package Better.Image.Handler
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Image_Handler_Gd extends Better_Image_Handler_Base
{
	
	public function genSCode($code,$w=100,$h=40)
	{
		$aimg = imagecreate($w,$h);
		$back = imagecolorallocate($aimg,255,255,255);
		$border = imagecolorallocate($aimg,0,0,0);
		imagefilledrectangle($aimg,0,0,$w - 1,$h - 1,$back);
		imagerectangle($aimg,0,0,$w - 1,$h - 1,$border);
	  
		for ($i=1; $i<=40;$i++)
		{
			$dot = imagecolorallocate($aimg,mt_rand(50,255),mt_rand(50,255),mt_rand(50,255));
			imagesetpixel($aimg,mt_rand(1,$w-1), mt_rand(1,$h-1),$dot);
		}

		for ($i=1; $i<=40;$i++)
		{
			imageString($aimg,1,$i*$w/10+mt_rand(1,3),mt_rand(1,15),'*',imageColorAllocate($aimg,mt_rand(150,255),mt_rand(150,255),mt_rand(150,255)));
		}
		
		for ($i=0;$i<strlen($code);$i++)
		{
			imageString($aimg,mt_rand(33,45),$i*$w/4+mt_rand(1,5)+5,mt_rand(1,6)+8,$code[$i],imageColorAllocate($aimg,mt_rand(50,255),mt_rand(0,120),mt_rand(50,255)));
		}

		ob_end_clean()	;
		header("Expires: 0");
		header("Pragma:no-cache");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-type: image/png");
		header("Content-Transfer-Encoding: binary");

		imagepng($aimg) ;
		imagedestroy($aimg);
	}
	
	public function crop($x1, $y1, $tw, $th, $nw, $nh)
	{
		$newFile = $this->file;
		
		$w = $this->info['w'];
		$h = $this->info['h'];
		$m = $this->info['mime'];
		$dir = $this->info['dirname'].'/';
		$fileName = $this->info['basename'];
				
		switch(parent::$map[$m]) {
			case 'gif':
				$image = imagecreatefromgif($this->file);
				break;
			case 'jpg':
				$image = imagecreatefromjpeg($this->file);
				break;
			case 'png':
				$image = imagecreatefrompng($this->file);
				break;
		}
		
		if ($image) {
			$ni = imagecreatetruecolor($nw, $nh);
			//bool imagecopyresampled
			// ( resource $dst_image  , resource $src_image  , int $dst_x  , int $dst_y  , int $src_x  , int $src_y  ,
			// int $dst_w  , int $dst_h  , int $src_w  , int $src_h  )
			imagecopyresampled($ni, $image, 0, 0, $x1, $y1, $nw, $nh, $tw, $th);
			
			$tmp = pathinfo($fileName);
			$newFile = $dir.'crop_'.$tmp['filename'].'.'.$this->info['extension'];
								
			imagejpeg($ni, $newFile);
			chmod($newFile, 0777);
			imagedestroy($ni);
			imagedestroy($image);
		}
		
		return $newFile;
	}
	
	/**
	 * 生成图片缩略
	 * 
	 * @param $width 最大宽度
	 * @param $height 最大高度
	 * @param $prefix 新文件名的前缀
	 * @return string
	 */
	public function scale($width, $height, $prefix='')
	{
		$newFile = $this->info['dirname'].'/'.$prefix.$this->info['filename'].'.'.$this->info['extension'];

		$im = parent::scaleImage(array(
			'w' => $this->info['width'],
			'h' => $this->info['height'],
			'mw' => $width,
			'mh' => $height
			));
		$destWidth = $im['w'];
		$destHeight = $im['h'];			
		if ($this->info['width']<=$width && $this->info['height']<=$height && $this->info['width'] && $this->info['height']) {
			copy($this->file, $newFile);
		} else {
			
			$image = null;
			$func = '';
			switch(self::$map[$this->info['mime']]) {
				case 'gif':
					$image = imagecreatefromgif($this->file);
					$func = 'imagegif';
					break;
				case 'jpg':
					$image = imagecreatefromjpeg($this->file);
					$func = 'imagejpeg';
					break;
				case 'png':
					$image = imagecreatefrompng($this->file);
					$func = 'imagepng';
					break;
				case 'bmp':
					$image = imagecreatefromwbmp($this->file);
					$func = 'imagewbmp';
					break;
			}
	
			if ($image) {
				$thumb = imagecreatetruecolor($destWidth, $destHeight);
				imagecopyresampled($thumb, $image, 0, 0, 0, 0, $destWidth, $destHeight, $this->info['width'], $this->info['height']);
				
				$func($thumb, $newFile);
				chmod($newFile, 0777);
				imagedestroy($image);
				imagedestroy($thumb);
			} else {
				Better_Log::getInstance()->logInfo('Image_Failed:['.serialize($this->info).']', 'image');
				$newFile = false;
			}
		}
		
		return $newFile;
	}
	
	/**
	 * 旋转图片
	 * 
	 * @param unknown_type $angle
	 */
	public function rotate($angle)
	{
		$newFile = '';
		$image = null;
		$func = '';
		
		switch(self::$map[$this->info['mime']]) {
			case 'gif':
				$image = imagecreatefromgif($this->file);
				$func = 'imagegif';
				break;
			case 'jpg':
				$image = imagecreatefromjpeg($this->file);
				$func = 'imagejpeg';
				break;
			case 'png':
				$image = imagecreatefrompng($this->file);
				$func = 'imagepng';
				break;
			case 'bmp':
				$image = imagecreatefromwbmp($this->file);
				$func = 'imagewbmp';
				break;
		}

		if ($image) {
			$rotate = imagerotate($image, $angle, 0);			
			$func($rotate, $this->file);
			
			imagedestroy($image);
			imagedestroy($rotate);
			
			$newFile = $this->file;
			@chmod($newFile, 0777);
		} else {
			Better_Log::getInstance()->logInfo('Image_Rotate_Failed:['.serialize($this->info).']', 'image_rotate');
			$newFile = false;
		}		
		
		return $newFile;
	}
	
}