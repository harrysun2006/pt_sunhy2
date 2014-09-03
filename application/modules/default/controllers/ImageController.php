<?php

/**
 * 图片
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class ImageController extends Better_Controller_Front
{

	public function init()
	{
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 1);
		parent::init();
	}
	
	public function indexAction()
	{
		$fid = $this->getRequest()->getParam('fid', '');
		if (!$fid) {
			$uid = $this->getRequest()->getParam('uid', '');
			$hash = $this->getRequest()->getParam('hash', '');
			$fid = $uid.'.'.$hash;
		}

		$type = $this->getRequest()->getParam('type', 'normal');

		if ($fid) {
			$data = Better_Attachment::getInstance($fid)->parseAttachment();
			if (isset($data['file_id']) && $data['file_id']) {
				$save_path = $data['save_path'];
				$filezie = $data['filesize'];
				$filename = $data['filename'];
				$mimetype = $data['mimetype'];
				$thumb_save_path = $data['thumb_save_path'];
				$tiny_save_path = $data['tiny_save_path'];

				switch($type) {
					case 'normal':
						$file = $save_path;
						break;
					case 'thumb':
						$mimetype = 'image/jpeg';
						$file = $thumb_save_path;
						break;
					case 'tiny':
						$mimetype = 'image/jpeg';
						$file = $tiny_save_path;
						break;
				}

				if (file_exists($file)) {
					$filesize = filesize($file);

					$this->getResponse()->setHeader('Content-Length', $filesize);
					$this->getResponse()->setHeader('Content-Type', $mimetype);
					$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"');

					$this->getResponse()->sendHeaders();

					echo file_get_contents($file);
					exit(0);
				} else {
					Better_Log::getInstance()->logAlert('FILE_NOT_FOUND_ON_SERVER:['.$fid.','.$type.']', 'attachment');
				}
			} else {
				Better_Log::getInstance()->logAlert('FILE_NOT_FOUND_IN_DB:['.$fid.','.$type.']', 'attachment');
			}
			
		} else {
			Better_Log::getInstance()->logAlert('FILE_NOT_FOUND_FID:['.$fid.','.$type.']', 'attachment');
		}
	}


	public function avatarAction()
	{
		$fid = $this->getRequest()->getParam('fid', '');
		if (!$fid) {
			$uid = $this->getRequest()->getParam('uid', '');
			$hash = $this->getRequest()->getParam('hash', '');
			$fid = $uid.'.'.$hash;
		} else {
			list($uid, $hash) = explode('.', $fid);
		}

		$type = $this->getRequest()->getParam('type', 'normal');

		if ($fid) {
			$result = Better_User_Avatar::getInstance($uid)->parse();
				$save_path = $result['save_path'];
				$thumb_save_path = $result['thumb_save_path'];
				$tiny_save_path = $result['tiny_save_path'];
				$filesize = $result['filesize'];
				$mimetype = 'image/jpeg';

				switch($type) {
					case 'normal':
						$file = $save_path;
						break;
					case 'thumb':
						$file = $thumb_save_path;
						break;
					case 'tiny':
						$file = $tiny_save_path;
						break;
				}

				if (file_exists($file)) {
					$filesize = filesize($file);
					$this->getResponse()->setHeader('Content-Length', $filesize);
					$this->getResponse()->setHeader('Content-Type', $mimetype);

					$this->getResponse()->sendHeaders();

					echo file_get_contents($file);
					exit(0);
				} else {
					Better_Log::getInstance()->logAlert('AVATAR_NOT_FOUND_ON_SERVER:['.$fid.','.$type.','.$save_path.']', 'attachment');
				}

		} else {
			Better_Log::getInstance()->logAlert('AVATAR_NOT_FOUND_FID:['.$fid.','.$type.']', 'attachment');
		}
	}
}

?>