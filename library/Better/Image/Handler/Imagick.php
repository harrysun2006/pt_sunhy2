<?php

/**
 * imagick图片处理
 * 
 * @package Better.Image.Handler
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Image_Handler_Imagick extends Better_Image_Handler_Base
{
	protected $imagick = null;
	
	public function __construct($file)
	{
		parent::__construct($file);

		if ($this->file) {
			$this->imagick = new Imagick($file);
		}
	}
	
	function __destruct()
	{
		if ($this->imagick) {
			$this->imagick->clear();
			$this->imagick->destroy();
		}
	}
	
	public function genSCode($code,$w=100,$h=40)
	{
		$sizes = array(28, 29, 30, 31, 32 ,33, 34, 35, 36);
		$angles = array();
		for($i=-8; $i<=8; $i++) {
			$angles[] = $i;
		}
		$bgColors = array('#ffffff');
		$colors = array('red', 'black', 'darkgreen', 'orange', 'darkblue');
		$fonts = explode('|', Better_Config::getAppConfig()->scode->fonts);
		
		$im = new Imagick();
		$im->newPseudoImage($w, $h, "gradient:red-green");		
 		$bg = new ImagickPixel();
 		$bg->setColor( $bgColors[rand(0, count($bgColors)-1)] );
 		
 		$ImagickDraw = new ImagickDraw();
 		$ImagickDraw->setFont($fonts[rand(0, count($fonts)-1)]);
 		$ImagickDraw->setFontSize( $sizes[rand(0, count($sizes)-1)] );
		$ImagickDraw->pushPattern('gradient', 0, 0, $w, $h);
		$ImagickDraw->composite(41, 0, 0, $w, $h, $im);
		$ImagickDraw->popPattern();
		$ImagickDraw->setFillPatternURL('#gradient');
		$ImagickDraw->setFillColor($colors[rand(0, count($colors)-1)]);
		
 		$Imagick = new Imagick();
 		$Imagick->newImage( $w, $h, $bg );
 		$Imagick->borderImage('black', 1, 1);
 		$Imagick->annotateImage( $ImagickDraw, 12, 34, $angles[rand(0, count($angles)-1)], $code );
 		$Imagick->swirlImage( 10 );
 		
 		for($i=0; $i<20; $i++) {
 			$ImagickDraw->line( mt_rand( 1, $w-1 ), mt_rand(1, $h-1), mt_rand(1, $w-1), mt_rand(1, $h-1) );
 		}
 		
 		for ($i=0; $i<50; $i++) {
 			$ImagickDraw->color( mt_rand(1, $w-1), mt_rand(1, $h-1), 0);
 		}

	 	$Imagick->drawImage( $ImagickDraw );
	 	$Imagick->setImageFormat( 'png' );
	 	
		header("Pragma:no-cache");
		header("Cache-control:no-cache");
		header("Content-type: image/png");
 		echo $Imagick->getImageBlob( );
 		
 		$Imagick->clear();
 		$Imagick->destroy();
		exit(0); 		
	}	
	
	public function crop($x1, $y1, $tw, $th, $nw, $nh)
	{
		$newFile = $this->file;
		
		$w = $this->info['w'];
		$h = $this->info['h'];
		$m = $this->info['mime'];
		$dir = $this->info['dirname'].'/';
		$fileName = $this->info['basename'];
		
		if ($this->imagick) {
			$thumbGif = Better_Config::getAppConfig()->thumb_gif;

			if (self::$map[$this->info['mime']]=='gif' && strtolower($this->info['extension'])=='gif') {
				foreach ($this->imagick as $frame) {
					$frame->cropImage($tw, $th, $x1, $y1);
					$frame->setImagePage($width, $height, 0, 0);
					
					if (!$thumbGif) {
						$newFile = $dir.'crop_'.$fileName;
						$frame->writeImage($newFile);
						return $newFile;
					}
				}
				$thumbGif && $this->imagick->writeImages($newFile, true);					
			} else {		

				
				$this->imagick->cropThumbnailImage($nw, $nh);
				
			}
			
			$newFile = $dir.'crop_'.$fileName;
			$this->imagick->writeImage($newFile);
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
				
		if ($this->info['width']<=$width && $this->info['height']<=$height && $this->info['width'] && $this->info['height'] ) {
			copy($this->file, $newFile);
		} else {
			$thumbGif = Better_Config::getAppConfig()->thumb_gif;

			if (self::$map[$this->info['mime']]=='gif') {
				foreach ($this->imagick as $frame) {
					$frame->thumbnailImage($destWidth, $destHeight, true);
					$frame->setImagePage($destWidth, $destHeight, 0, 0);
					
					if (!$thumbGif) {
						$frame->writeImage($newFile);
						return $newFile;
					}
				}
				$thumbGif && $this->imagick->writeImages($newFile, true);				
			} else {
				$this->imagick->thumbnailImage($destWidth, $destHeight, true);
				$this->imagick->writeImage($newFile);
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
		$this->imagick->rotateImage(new ImagickPixel(), 90);
		$this->imagick->writeImage($this->file);
	}	
	
	public function genThumb(&$params)
	{
		$thumbFile = '';
		
		if (parent::$map[$this->fileObj->info['m']]=='gif') {
			$thumbFile = $this->_genGifThumb($params);
		} else {
			$thumbFile = $this->_genThumb($params);
		}
		
		if ($thumbFile && file_exists($thumbFile)) {
			@chmod($thumbFile, 0777);
		}
		
		return $thumbFile;
	}
	
	private function _genThumb(&$params)
	{
		$thumbFile = '';
		
		$dir = dirname($this->fileObj->file);
		$fileName = basename($this->fileObj->file);
		$prefix = $params['prefix'] ? $params['prefix'] : '';
		
		if ($this->fileObj->info['h']>$params['mh']) {
			$this->imagick->thumbnailImage(0, $params['mh']);
		}
		
		if ($this->imagick->getImageWidth()>$params['mw']) {
			$this->imagick->thumbnailImage($params['mw'], 0);
		}
		
		if ($prefix=='') {
			$thumbFile = $this->fileObj->file;
		} else {
			$tmp = pathinfo($this->fileObj->file);
			$thumbFile = $dir. $prefix. $tmp['filename'].'.'.$tmp['extension'];
		}
		
		$this->imagick->writeImage($thumbFile);

		return $thumbFile;
	}
	
	private function _genGifThumb(&$params)
	{
		$dir = dirname($this->fileObj->file).'/';
		$fileName = basename($this->fileObj->file);
		$prefix = $params['prefix'] ? $params['prefix'] : '';
		
		if ($prefix=='') {
			$thumbFile = $this->fileObj->file;
		} else {
			$tmp = pathinfo($fileName);
			$thumbFile = $dir. $prefix. $tmp['filename'].'.'.$tmp['extension'];
		}
		
		$imagick = new Imagick();
		$ct = new ImagickPixel('transparent');
		
		foreach ($this->imagick as $gifFrame) {
			$page = $gifFrame->getImagePage();
			$tmp = new Imagick();
			$tmp->newImage($page['width'], $page['height'], $ct, 'gif');
			$tmp->compositeImage($gifFrame, Imagick::COMPOSITE_OVER, $page['x'], $page['y']);
			
			if ($this->fileObj->info['h']>$params['mh']) {
				$tmp->thumbnailImage(0, $params['mh']);
			}
			
			if ($tmp->getImageWidth()>$params['mw']) {
				$tmp->thumbnailImage($params['mw'], 0);
			}
			
			$imagick->addImage($tmp);
			$imagick->setImagePage($tmp->getImageWidth(), $tmp->getImageHeight(), 0, 0);
			$imagick->setImageDelay($gifFrame->getImageDelay());
			$imagick->setImageDispose($gifFrame->getImageDispose());
		}
		
		$imagick->coalesceImages();
		$imagick->writeImages($thumbFile, true);
		$imagick->destroy();

		return $thumbFile;
	}
}