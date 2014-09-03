<?php

/**
 * 附件上传操作
 * 
 * @package Better.Attachment
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Attachment_Save_Csv extends Better_Attachment_Base
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
	protected static function getSequnce()
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
		$basePath =self::$config->attach_tmp;		
		$savePath = $basePath;

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

		if ($file['size']>self::$config->global->max_size) {
			Better_Log::getInstance()->logAlert('UPLOAD_FAILED_'.self::$FILE_TO_LARGE, 'attachment');
			return self::$FILE_TO_LARGE;
		}
		
		$newFileName = Better_Registry::get('sess')->get('uid').'.'.$seq.'.'.$ext;
		$newFile = $savePath.'/'.$newFileName;
		
		$flag = move_uploaded_File($file['tmp_name'], $newFile);
		if (!$flag) {
			Better_Log::getInstance()->logEmerge('UPLOAD_FAILED_'.self::$UNKNOWN, 'attachment');
			return self::$UNKNOWN;
		}
		/*
		$handler = Better_Image_Handler::factory($newFile);
		$newFile = $handler->scale(self::$config->global->image->max_width, self::$config->global->image->max_height);
		$thumbFile = Better_Image_Handler::factory($newFile)->scale(self::$config->global->image->thumb_width, self::$config->global->image->thumb_height, self::$config->global->image->thumb_prefix);
		$tinyFile = Better_Image_Handler::factory($newFile)->scale(self::$config->global->image->tiny_width, self::$config->global->image->tiny_height, self::$config->global->image->tiny_prefix);
		*/
		self::$result[$this->name]['save_path'] = $newFile;
		//self::$result[$this->name]['thumb_save_path'] = $thumbFile;
		//self::$result[$this->name]['tiny_save_path'] = $tinyFile;
		
		$uid = Better_Registry::get('sess')->get('uid');
		$file_id = $uid.'.'.$seq;
		
		$d = array();
		$d['dateline'] = time();
		$d['filename'] = $file['name'];
		$d['filesize'] = $file['size'];
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
	
	/**
	 * 返回上传的结果
	 * 
	 * @return array
	 */
	public function result()
	{
		return self::$result[$this->name];
	}
}