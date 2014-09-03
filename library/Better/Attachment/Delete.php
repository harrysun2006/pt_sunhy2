<?php

/**
 * 附件删除操作
 * 
 * @package Better.Attachment
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Attachment_Delete extends Better_Attachment_Base
{
	protected $fid = '';
	protected static $instance = array();
	protected $info = array();
	
	protected function __construct($fid)
	{
		$this->fid = $fid;
		parent::__construct();
	}
	
	public function getInstance($fid)
	{
		if (!isset(self::$instance[$fid])) {
			self::$instance[$fid] = new self($fid);
		}
		
		return self::$instance[$fid];
	}
	
	/**
	 * 执行删除操作
	 * 
	 * @return bool
	 */
	public function delete()
	{
		$flag = false;
		
		if ($this->fid) {
			$data = Better_Attachment_Parse::parse($this->fid);
			$uid = Better_Registry::get('sess')->get('uid');
			$admin_uid = Better_Registry::get('sess')->get('admin_uid');
			
			if ($data['file_id'] && ($data['uid']==$uid || $admin_uid)) {
				list($uid, $seq) = explode('.', $data['file_id']);
				$file = $data['save_path'];
				file_exists($file) && unlink($file);
				
				$thumb = $data['thumb_save_path'];
				file_exists($thumb) && unlink($thumb);
				
				$tiny = $data['tiny_save_path'];
				file_exists($tiny) && unlink($tiny);
				
				Better_DAO_Attachment::getInstance($uid)->delete($this->fid);
				Better_DAO_AttachAssign::getInstance()->deleteByCond(array(
					'fid' => $this->fid
					));
					
				Better_Cache::remote()->set('kai_attach_'.$uid.'_'.$seq, null);
				Better_Hook::factory(array(
					'Tracelog'
				))->invoke('AttachmentDeleted', array(
				));
					
				$flag = true;
			}
		}
		
		return $flag;

	}
}