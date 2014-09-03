<?php

/**
 * 附件处理
 *
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Attachment 
{
	
	/**
	 * 附件相关配置
	 *
	 * @var array
	 */
	public static $config = array();
	
	/**
	 * 附件实例数组
	 *
	 * @var array
	 */
	private static $instance = array();
	private $fid = null;

	/**
	 * 附件上传的根路径，对应服务器上的一个文件夹
	 * @var string
	 */
	protected $basePath = '';
	
	/**
	 * 附件最终的保存路径，由basePath和hash过后的路径组成
	 *
	 * @var string
	 */
	protected $filePath = '';
	
	/**
	 * 允许的扩展名
	 * @var array
	 */
	protected $allowedExt = array();
	
	/**
	 * 一次post最大能提交几个文件（Zend_Framework需要的）
	 * @TODO 是否可以去掉了？
	 * @var unknown_type
	 */
	protected $maxFileCount = 1;
	
	/**
	 * 单个附件所允许的最大尺寸
	 * @var integer
	 */
	protected $maxSize = 0;
	
	protected $dao = null;
	
	/**
	 * 图片允许的最大宽度
	 * @var integer
	 */
	protected $maxWidth = 0;
	
	/**
	 * 图片允许的最大高度
	 * @var integer
	 */
	protected $maxHeight = 0;
	
	/**
	 * 要生成的缩略图的宽度
	 * @var integer
	 */
	protected $thumbWidth = 0;
	
	/**
	 * 要生成的缩略图的高度
	 * @var integer
	 */
	protected $thumbHeight = 0;
	
	/**
	 * 附件上传的返回值
	 * @var integer
	 */
	protected $CODE = -1;
	protected static $UNKNOWN = 1000;							//	未知错误
	protected static $FILE_TO_LARGE = 1001;				//	文件尺寸过大
	protected static $EXT_NOT_PERMITTED = 1002;		//	扩展名不被允许
	protected static $MIME_NOT_PERMITTED = 1003;	//	mime类型不被允许
	protected static $FILE_NOT_EXISTS = 1004;				//	附件保存的目录不存在或无法写入
	
	protected static $tiny_width = 80;
	protected static $tiny_height = 60;
	
	protected $passby_db = false;

	function __construct($fid=0)
	{
		$this->fid = $fid;
		
		if (count(self::$config)==0) {
			self::$config = &Better_Config::getAttachConfig();
		}
	}
	
	/**
	 * 初始化一些变量
	 *
	 * @return null
	 */
	public function init()
	{
	
		$basePath = @self::$config->{'attach_server_'.$this->dao->serverId}->save_path;
		$this->basePath = $basePath ? $basePath : APPLICATION_PATH.'/../public/files';
		$this->filePath = $this->basePath;
		$this->allowedExt = explode(',', self::$config->global->allowed_ext);
		$this->maxFileCount = self::$config->global->max_file_count ? intval(self::$config->global->max_file_count) : $this->maxFileCount;
		$this->maxSize = self::$config->global->max_size;
		self::$tiny_width = self::$config->global->image->tiny_width;
		self::$tiny_height = self::$config->global->image->tiny_height;
	}

    public static function getInstance($fid=0)
    {
    	$fid=='' && $fid=0;
        if (!array_key_exists($fid, self::$instance)) {
            self::$instance[$fid] = new Better_Attachment($fid);
            self::$instance[$fid]->init();
        }
        return self::$instance[$fid];
    }
    
    public function __call($method, $params)
    {
    	$className = 'Better_Attachment_'.ucfirst(strtolower($method));
		
		if (class_exists($className)) {
			return call_user_func(array($className, 'getInstance'), $this->fid);
		} else {
			return null;
		}  	
    }

    /**
     * 获取配置
     *
     * @return object
     */
	public function getConfig()
	{
		return self::$config;
	}

    public function getFiles($fids)
    {
    }

    /**
     * 备份文件
     *
     * @param $file_id
     * @return unknown_type
     */
	public function cpFile($file_id='') 
	{
		$file_id = $file_id=='' ? $this->fid : $file_id;
		$d = $this->dao->get($file_id);
		if ($d['file_id']) {
			$path = $this->hashDir($d['uid'], @self::$config->{'attach_server_'.$this->dao->serverId}->save_path);
			$file = $path.'/'.$d['file_id'].($d['ext'] ? '.'.$d['ext'] : '');
			$file_new = $file . '.bak';
			file_exists($file) && copy($file,$file_new);
			
			$thumb = $path.'/thumb_'.$d['file_id'].'.jpg';
			$thumb_new = $thumb . '.bak';
			file_exists($thumb) && copy($thumb,$thumb_new);
			
			$tiny = $path.'/tiny_'.$d['file_id'].'.jpg';
			$tiny_new = $tiny . '.bak';
			file_exists($tiny) && copy($tiny,$tiny_new);
		}
	}

	/**
	 * 删除头像
	 *
	 * @param string $fid
	 * @param integer $uid
	 * @return bool
	 */
	public static function delAvatar($fid, $uid)
	{
		$result = false;
		$rt = Better_DAO_AttachAssign::getInstance()->findFid($fid);
		$sid = $rt['sid'];

		if ($sid) {
			$path = self::hashDir($uid, self::$config->{'attach_server_'.$sid}->save_path.'/avatar');
			file_exists($path.'/'.$fid.'.jpg') && unlink($path.'/'.$fid.'.jpg');
			file_exists($path.'/thumb_'.$fid.'.jpg') && unlink($path.'/thumb_'.$fid.'.jpg');
			file_exists($path.'/tiny_'.$fid.'.jpg') && unlink($path.'/tiny_'.$fid.'.jpg');
			Better_DAO_AttachAssign::getInstance()->delAssign($fid);
			
			$result = true;
		}
		
		return $result;
	}

	
	/**
	 * 上传一个文件
	 *
	 * @param $name
	 * @return integer
	 */
	public function uploadFile($name)
	{
		$newFile = Better_Attachment_Save::getInstance($name)->upload();
		if (!is_numeric($newFile)) {
			return self::getInstance($newFile);
		} else {
			return $newFile;
		}
	}

	/**
	 * 解析附件地址
	 * 可传入附件的file_id，以及从数据库已经取出的datarow
	 *
	 * @param $file_id
	 * @return array
	 */
	public function parseAttachment($file_id='')
	{
		$file_id = $file_id=='' ? $this->fid : $file_id;
		return Better_Attachment_Parse::getInstance($file_id)->result();
	}
	
	/**
	 * 格式化文件大小
	 * 
	 * @param $size
	 * @return string
	 */
	public static function formatSize($size)
	{
		$size = (int)$size;
		$str = $size;
		
		if ($size>=1024*1024) {
			$str = sprintf("%.2f", $size/(1024*1024)).' MB';
		} else if ($size>=1024) {
			$str = sprintf("%.2f", $size/1024).' KB';
		} else if ($size>0) {
			$str = $size.' B';
		}
		
		return $str;
	}
}

?>