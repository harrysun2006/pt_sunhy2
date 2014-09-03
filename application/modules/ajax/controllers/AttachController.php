<?php

/**
 * 附件相关
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Ajax_AttachController extends Better_Controller_Ajax
{
	public function init()
	{
		parent::init();	
	}		
	
	/**
	 * 上传一个附件
	 * 
	 * @return 
	 */
	public function uploadAction()
	{
		$post = $this->getRequest()->getPost();
		$attach = $post['attach'];

		$id = Better_Attachment_Save::getInstance('myfile')->upload();
		$this->output['new_file_url'] = '';
		
		if (strpos($id, '.')) {
			if ($attach) {
				Better_Attachment_Delete::getInstance($attach)->delete();
			}
			
			$attachInfo = Better_Attachment_Parse::parse($id);
			$this->output['new_file_url'] = $attachInfo['thumb'];
			
			$this->output['has_err'] = 0;
			$this->output['attach'] = $id;				
		} else {
			$this->output['has_err'] = 1;
			$this->output['err'] = $id;
		}		
		
		$this->output();
	}
	
	/**
	 * 删除一个附件
	 * 
	 * @return
	 */
	public function deleteAction()
	{
		$post = $this->getRequest()->getPost();
		$attach = $post['attach'];
		$this->output['err'] = '';
		
		if ($attach) {
			$data = Better_Attachment_Parse::getInstance($attach)->result();
			if ($data['uid']==$this->uid) {
				Better_Attachment_Delete::getInstance($attach)->delete();
			} else {
				$this->output['err'] = 'WRONG_RIGHTS';
			}
		} else {
			$this->output['err'] = 'WRONG_FILE_ID';
		}		
		
		$this->output();
	}
}