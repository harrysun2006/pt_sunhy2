<?php

/**
 * 附件上传操作
 * 
 * @package Better.Attachment
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Attachment_Save extends Better_Attachment_Base
{
	protected $name = '';
	
	/**
	 * 附件上传的返回值
	 * @var integer
	 */
	protected $CODE = -1;
	public static $UNKNOWN = 1000;							//	未知错误
	public static $FILE_TO_LARGE = 1001;				//	文件尺寸过大
	public static $EXT_NOT_PERMITTED = 1002;		//	扩展名不被允许
	public static $MIME_NOT_PERMITTED = 1003;	//	mime类型不被允许
	public static $FILE_NOT_EXISTS = 1004;				//	附件保存的目录不存在或无法写入
	public static $PATH_NOT_EXISTS = 1005;			// 附件保存目录不存在
	public static $NOT_SUPPORTED_IMAGE = 1006;	//	不支持的图片格式
	public static $URL_IS_NULL = 1007;           //上传的图片链接为空
	public static $IMG_LINK_WRONG = 1008;        //图片链接过长或不能生成图片
		
	protected static $instance = array();
	protected static $result = array();
	
	protected function __construct($name)
	{
		$this->name = $name;
		parent::__construct();
	}
	
	public static function getInstance($name)
	{
		if (self::$instance[$name]==null) {
			self::$instance[$name] = new self($name);
		}	
		
		return self::$instance[$name];
	}
	
	/**
	 * 获得当前的附件序列
	 * 
	 * @return integer
	 */
	public static function getSequnce()
	{
		return Better_DAO_AttachSequence::getInstance()->get();
	}
	
	/**
	 * 更新附件序列
	 * 
	 * @return null
	 */
	protected static function raiseSequnce()
	{
		
	}
	
	/**
	 * 获取附件保存的下一个服务器
	 * 
	 * @return integer
	 */
	protected static function nextServer()
	{

	}
	
	/**
	 * 上传一个附件
	 * 
	 * @return misc
	 */
	public function upload($basePath='')
	{
		$seq = self::getSequnce();
		
		$servers = (int) self::$config->global->servers;
		$basePath = self::$config->{'attach_server_'.$servers}->save_path.$basePath;
		$savePath = self::hashDir($seq, $basePath, true);

		$file = array();
		
		if (!is_dir($savePath)) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$PATH_NOT_EXISTS.':['.$savePath.']', 'attachment');
			return self::$PATH_NOT_EXISTS;
		}
		
		if (isset($_FILES[$this->name])) {
			$file = &$_FILES[$this->name];
		} else {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$FILE_NOT_EXISTS, 'attachment');
			return self::$FILE_NOT_EXISTS;
		}
		
		$fileInfo = pathinfo($file['name']);
		$ext = strtolower($fileInfo['extension']);
		if (!in_array($ext, explode(',', self::$config->global->allowed_ext))) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$EXT_NOT_PERMITTED.', ext:['.$ext.'], allowed:['.self::$config->global->allowed_ext.']', 'attachment');
			return self::$EXT_NOT_PERMITTED;
		}

		if (!preg_match('/^image/i', $file['type'])) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$MIME_NOT_PERMITTED.':['.$file['type'].']', 'attachment');
			return self::$MIME_NOT_PERMITTED;
		}
		
		if ($file['size']>self::$config->global->max_size) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$FILE_TO_LARGE, 'attachment');
			return self::$FILE_TO_LARGE;
		}
		
		//	Gif特殊化处理
		if (Better_Config::getAppConfig()->gif->spec && preg_match('/gif/i', strtolower($file['type'])) && $file['size']>Better_Config::getAppConfig()->gif->maxsize) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$FILE_TO_LARGE, 'attachment');
			return self::$FILE_TO_LARGE;			
		}
		
		$newFileName = Better_Registry::get('sess')->get('uid').'.'.$seq.'.'.$ext;
		$newFile = $savePath.'/'.$newFileName;
		
		$flag = move_uploaded_File($file['tmp_name'], $newFile);
		$hash = md5_file($newFile);
		if (!$flag) {
			Better_Log::getInstance()->logEmerge('UPLOAD_FAILED_'.self::$UNKNOWN, 'attachment');
			return self::$UNKNOWN;
		}
		
		if (defined('IN_API') && !preg_match('/gif/i', strtolower($file['type']))) {
			$rotates = Better_Registry::get('image_rotates');
			if (isset($rotates[$this->name])) {
				$newFile = Better_Image_Handler::factory($newFile, 'gd')->rotate($rotates[$this->name]);
			}
		}
		
		$handler = Better_Image_Handler::factory($newFile);
		
		$origFile = $handler->scale(self::$config->global->image->max_width, self::$config->global->image->max_height);
		if ($origFile===false) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$NOT_SUPPORTED_IMAGE.':['.$file['type'].']', 'attachment');
			unlink($newFile);
			return self::$NOT_SUPPORTED_IMAGE;			
		}
		
		$thumbFile = Better_Image_Handler::factory($origFile)->scale(self::$config->global->image->thumb_width, self::$config->global->image->thumb_height, self::$config->global->image->thumb_prefix);
		if ($thumbFile===false) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$NOT_SUPPORTED_IMAGE.':['.$file['type'].']', 'attachment');
			unlink($newFile);
			unlink($origFile);
			return self::$NOT_SUPPORTED_IMAGE;			
		}
				
		$tinyFile = Better_Image_Handler::factory($origFile)->scale(self::$config->global->image->tiny_width, self::$config->global->image->tiny_height, self::$config->global->image->tiny_prefix);
		if ($tinyFile===false) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$NOT_SUPPORTED_IMAGE.':['.$file['type'].']', 'attachment');
			unlink($newFile);
			unlink($origFile);
			unlink($thumbFile);
			return self::$NOT_SUPPORTED_IMAGE;			
		}
				
		self::$result[$this->name]['save_path'] = $origFile;
		self::$result[$this->name]['thumb_save_path'] = $thumbFile;
		self::$result[$this->name]['tiny_save_path'] = $tinyFile;
		
		$uid = Better_Registry::get('sess')->get('uid');
		$file_id = $uid.'.'.$seq;
		
		$d = array();
		$d['dateline'] = time();
		$d['filename'] = $file['name'];
		$d['filesize'] = $file['size'];
		$d['ext'] = $ext;
		$d['uid'] = $uid;
		$d['file_id'] = $file_id;
		$d['hash'] = $hash;

		Better_DAO_Attachment::getInstance($uid)->insert($d);
			
		Better_DAO_AttachAssign::getInstance()->insert(array(
			'fid' => $d['file_id'],
			'sid' => $servers,
			));	
	
		Better_Hook::factory(array(
			'Tracelog'
		))->invoke('AttachmentUploaded', array(
			'file_id' => $file_id,
			'uid' => $uid
			));		

		return $file_id;
	}
	
	/**
	 * 返回上传的结果
	 * 
	 * @return array
	 */
	public function result()
	{
		return self::$result[$this->name];
	}
	
	
	/**
	 * 上传一个链接附件
	 * 
	 * @return misc
	 */
	public function uploadImgLink($url, $ext, $basePath='', $uid='')
	{
		$seq = self::getSequnce();
		$servers = (int) self::$config->global->servers;
		$basePath = self::$config->{'attach_server_'.$servers}->save_path.$basePath;
		$savePath = self::hashDir($seq, $basePath, true);
		if (!is_dir($savePath)) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$PATH_NOT_EXISTS.':['.$savePath.']', 'attachment');
			return self::$PATH_NOT_EXISTS;
		}
		
		$_uid = $uid ? $uid :  Better_Registry::get('sess')->get('uid');
		if($url){
			$ext = $ext?  $ext : strrchr($url, '.');
			
			$newFileName = $_uid . '.' . $seq . '.' . $ext;
			$newFile = $savePath.'/'.$newFileName;
			
			$url = str_replace(' ', '', $url);

      // file_get_contents() 或 curl_exec() 在解析域名时似乎存在性能问题。
      // 发现下载新浪头像只需0sec，下载腾讯头像却需10sec，用解析好的IP则都是0sec。
      // 所以这里先用 gethostbyname() 解析域名，然后用IP替换 URL 中的域名。
      $domain = preg_match('@^(?:http://)?([^/]+)@i',
            $url, $matches);
      $host = $matches[1];
      $ip = gethostbyname($host);
      $url = str_replace($host, $ip, $url);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host:$host"));
      $img = curl_exec($ch);

			if ($img) {
				$fp=@fopen($newFile, "a");
				$flag = fwrite($fp, $img);
				fclose($fp);
			} else {
				Better_Log::getInstance()->logEmerge('UPLOAD_FAILED_'.self::$IMG_LINK_WRONG, 'attachment');
				return self::$IMG_LINK_WRONG;
			}
			
		} else {
			Better_Log::getInstance()->logEmerge('UPLOAD_FAILED_'.self::$URL_IS_NULL, 'attachment');
			return self::$URL_IS_NULL;
		}
		
		
		if (!$flag) {
			Better_Log::getInstance()->logEmerge('UPLOAD_FAILED_'.self::$UNKNOWN, 'attachment');
			return self::$UNKNOWN;
		}
		$handler = Better_Image_Handler::factory($newFile);		
		$origFile = $handler->scale(self::$config->global->image->max_width, self::$config->global->image->max_height);
		if ($origFile===false) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$NOT_SUPPORTED_IMAGE.$url, 'attachment');
			unlink($newFile);
			return self::$NOT_SUPPORTED_IMAGE;			
		}
		
		$thumbFile = Better_Image_Handler::factory($origFile)->scale(self::$config->global->image->thumb_width, self::$config->global->image->thumb_height, self::$config->global->image->thumb_prefix);
		if ($thumbFile===false) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$NOT_SUPPORTED_IMAGE.$url, 'attachment');
			unlink($newFile);
			unlink($origFile);
			return self::$NOT_SUPPORTED_IMAGE;			
		}
		$tinyFile = Better_Image_Handler::factory($origFile)->scale(self::$config->global->image->tiny_width, self::$config->global->image->tiny_height, self::$config->global->image->tiny_prefix);
		if ($tinyFile===false) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$NOT_SUPPORTED_IMAGE.$url, 'attachment');
			unlink($newFile);
			unlink($origFile);
			unlink($thumbFile);
			return self::$NOT_SUPPORTED_IMAGE;			
		}
				
		self::$result[$this->name]['save_path'] = $origFile;
		self::$result[$this->name]['thumb_save_path'] = $thumbFile;
		self::$result[$this->name]['tiny_save_path'] = $tinyFile;
		
		$uid = $_uid;
		$file_id = $uid.'.'.$seq;
		
		$d = array();
		$d['dateline'] = time();
		$d['filename'] = $url;
		$d['filesize'] = 0;
		$d['ext'] = $ext;
		$d['uid'] = $uid;
		$d['file_id'] = $file_id;

		Better_DAO_Attachment::getInstance($uid)->insert($d);
			
		Better_DAO_AttachAssign::getInstance()->insert(array(
			'fid' => $d['file_id'],
			'sid' => $servers,
			));	
	
		Better_Hook::factory(array(
			'Tracelog'
		))->invoke('AttachmentUploaded', array(
			'file_id' => $file_id,
			'uid' => $uid
			));		

		return $file_id;
	}
}
