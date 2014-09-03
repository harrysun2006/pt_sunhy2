<?php

/**
 * 用户头像
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Avatar extends Better_User_Base
{
	protected static $instance = array();
	protected static $parsed = array();
	protected static $attachConfig = array();
	protected $avatarIsDefault = false;

	public static function getInstance($uid)
	{
		if (count(self::$attachConfig)==0) {
			self::$attachConfig = Better_Config::getAttachConfig();	
		}
		
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}		
	
	/**
	 * 上传头像
	 * 
	 * @return array
	 */
	public function upload($url='', $ext='', $uid='')
	{
		if ($url) {
			$id = Better_Attachment_Save::getInstance('myfile')->uploadImgLink($url, $ext, '/avatar', $uid);
		} else {
			$id = Better_Attachment_Save::getInstance('myfile')->upload('/avatar');
		}
		
		list($uid, $seq) = explode('.', $id);
		$result = $id;
		
		if ($uid && $seq) {
			$this->delete();

			$avatarResult = Better_Attachment_Save::getInstance('myfile')->result();
			self::$parsed[$this->uid]['save_path'] = $avatarResult['save_path'];
			self::$parsed[$this->uid]['thumb_save_path'] = $avatarResult['thumb_save_path'];
			self::$parsed[$this->uid]['tiny_save_path'] = $avatarResult['tiny_save_path'];
			
			Better_User::getInstance($this->uid)->avatar = $id;
			
			$result = $this->crop($id);
			$result['file_id'] = $id;
			
			$data_array = array();
	       	$data_array['uid'] = $this->uid;
			$data_array['username'] = $this->userInfo['username'];
	       	$data_array['refid'] = $result['file_id'];
	       	$data_array['type'] = 'avatar';
	       	$data_array['imgurl'] = $result['url'];
	       	$data_array['changetime'] = time();
	       	
	       	Better_DAO_Newimg::getInstance()->deleteByCond(array(
													'uid' =>$uid,
													'type' => 'avatar'));
	       	Better_DAO_Newimg::getInstance()->insert($data_array);
	       	
			Better_User::getInstance($this->uid)->updateUser(array(
				'avatar' => $id,
				'recommend'=>0
			));
			Better_DAO_Poi_Major::getInstance()->recommendAvatar($this->uid,0);//更换头像的时候需要重新推荐			
			Better_Cache::remote()->set('kai_user_avatar_'.$this->uid, null);			
		}
		
		return $result;
	}
	
	/**
	 * 裁剪头像
	 * 
	 * @return array
	 */
	protected function crop()
	{
		$data = $this->parse(true);
		$file = $data['save_path'];
		$config = Better_Config::getAttachConfig();
		
		$mw = $config->global->avatar->max_width;
		$mh = $config->global->avatar->max_height;
		$tw = $config->global->avatar->thumb_width;
		$th = $config->global->avatar->thumb_height;
		$tiw = $config->global->avatar->tiny_width;
		$tih = $config->global->avatar->tiny_height;
		$hw = $config->global->avatar->huge_width;
		$hh = $config->global->avatar->huge_height;

		list($w, $h) = getimagesize($file);
		$dw = $w;
		$dh = $h;
		$x1 = $y1 = 0;
		
		if ($w>$hw || $h>$hh) {
			if ($w>$h) {
				$x1 = ceil(($w-$h)/2);
				$y1 = 0;
				$dw = $h;
			} else {
				$x1 = 0;
				$y1 = ceil(($h-$w)/2);
				$dh = $w;
			}

		}



		$avatar = Better_Image_Handler::factory($file)->crop($x1, $y1, $dw, $dh, $hw, $hh);
		if (file_exists($avatar)) {
			$pathinfo = pathinfo($avatar);
			$avatarDir = $pathinfo['dirname'];
			
			$avatarFilename = str_replace('crop_', 'huge_', $pathinfo['basename']);
			$newAvatar = $avatarDir.'/'.$avatarFilename;
			copy($avatar, $newAvatar);
			
			$avatarFilename = str_replace('crop_', '', $pathinfo['basename']);
			$newAvatar = $avatarDir.'/'.$avatarFilename;
			copy($avatar, $newAvatar);			

			$normal = Better_Image_Handler::factory($newAvatar)->scale($mw, $mh, '');
			$thumb = Better_Image_Handler::factory($newAvatar)->scale($tw, $th, 'thumb_');
			$tiny = Better_Image_Handler::factory($newAvatar)->scale($tiw, $tih, 'tiny_');
			
			unlink($avatar);
			
			$result = array(
				'url' => $data['url'],
				'file' => $newAvatar
				);
		} else {
			Better_Log::getInstance()->logAlert('CROP_FAILED', 'image');
			$result = array('url'=>'', 'file'=>'xxxxxx');
		}

		return $result;
	}
	
	/**
	 * 解析头像
	 * 
	 * @param unknown_type $renew	强制重新解析
	 * @return array
	 */
	public function parse($renew=false)
	{
		$dbData = array();
	
		if (is_array($renew)) {
			$dbData = $renew;
		} 
		
		$data = $dbData['uid'] ? $dbData : $this->getUserInfo();
		!isset(self::$parsed[$data['uid']]) && self::$parsed[$data['uid']] = array();
		
		if ($renew===false && isset(self::$parsed[$data['uid']])) {
			return self::$parsed[$data['uid']];
		}	

		if ($data['avatar']) {
			self::$parsed[$data['uid']] = $dbData['file_id'] ? $dbData : Better_DAO_Attachment::getInstance($this->uid)->findFile_id($data['avatar']);
			//self::$parsed[$this->uid] = $dbData;
			if (isset(self::$parsed[$data['uid']]['file_id'])) {
				$tmp = Better_DAO_AttachAssign::getInstance()->get(self::$parsed[$data['uid']]['file_id']);
				$serverId = $tmp['sid'];
				
				list($uid, $seq) = explode('.', self::$parsed[$data['uid']]['file_id']);
				$ext = self::$parsed[$this->uid]['ext'];
				$serverUrl = self::$attachConfig->{'attach_server_'.$serverId}->url;
				$hash = Better_Attachment_Base::hashDir($seq);
				$basePath = self::$attachConfig->{'attach_server_'.$serverId}->save_path.'/avatar';
				$name = $data['avatar'].'.'.$ext;
				$hugeExists = file_exists($basePath.$hash.'/'.self::$attachConfig->global->image->huge_prefix.$name) ? true : false;
				
				if (self::$attachConfig->global->use_rewirte) {
					self::$parsed[$data['uid']]['url'] = $serverUrl.'/avatar/normal/'.$uid.'/'.$seq.'.'.$ext;
					self::$parsed[$data['uid']]['thumb'] = $serverUrl.'/avatar/thumb/'.$uid.'/'.$seq.'.'.$ext;
					self::$parsed[$data['uid']]['tiny'] = $serverUrl.'/avatar/tiny/'.$uid.'/'.$seq.'.'.$ext;
					self::$parsed[$data['uid']]['huge'] = $hugeExists ? $serverUrl.'/avatar/huge/'.$uid.'/'.$seq.'.'.$ext : self::$parsed[$data['uid']]['url'];
				} else {
					self::$parsed[$data['uid']]['url'] = $serverUrl.'/files/avatar'.$hash.'/'.$name;
					self::$parsed[$data['uid']]['thumb'] = $serverUrl.'/files/avatar'.$hash.'/'.Better_Config::getAttachConfig()->global->image->thumb_prefix.$name;
					self::$parsed[$data['uid']]['tiny'] = $serverUrl.'/files/avatar'.$hash.'/'.Better_Config::getAttachConfig()->global->image->tiny_prefix.$name;
					self::$parsed[$data['uid']]['huge'] = $hugeExists ? $serverUrl.'/files/avatar'.$hash.'/'.Better_Config::getAttachConfig()->global->image->huge_prefix.$name : self::$parsed[$data['uid']['url']];
				}

				self::$parsed[$data['uid']]['save_path'] = $basePath.$hash.'/'.$name;
				!file_exists(self::$parsed[$data['uid']]['save_path']) && self::$parsed[$data['uid']]['save_path'] = self::$attachConfig->global->avatar->default_save_path;
				
				self::$parsed[$data['uid']]['thumb_save_path'] = $basePath.$hash.'/'.self::$attachConfig->global->image->thumb_prefix.$name;
				!file_exists(self::$parsed[$data['uid']]['thumb_save_path']) && self::$parsed[$data['uid']]['thumb_save_path'] = self::$attachConfig->global->avatar->default_save_path;
				
				self::$parsed[$data['uid']]['tiny_save_path'] = $basePath.$hash.'/'.self::$attachConfig->global->image->tiny_prefix.$name;
				!file_exists(self::$parsed[$data['uid']]['tiny_save_path']) && self::$parsed[$data['uid']]['tiny_save_path'] = self::$attachConfig->global->avatar->default_save_path;

				self::$parsed[$data['uid']]['huge_save_path'] = $basePath.$hash.'/'.self::$attachConfig->global->image->huge_prefix.$name;
				!$hugeExists && self::$parsed[$data['uid']]['huge_save_path'] = self::$attachConfig->global->avatar->default_save_path;				
			} else {
				self::$parsed[$data['uid']]['url'] = self::$parsed[$data['uid']]['huge'] = self::$parsed[$data['uid']]['thumb'] = self::$parsed['tiny'] = Better_Config::getAttachConfig()->global->avatar->default_url;
			}
		} else {
			self::$parsed[$data['uid']]['url'] = self::$parsed[$data['uid']]['huge'] = self::$parsed[$data['uid']]['thumb'] = self::$parsed['tiny'] = Better_Config::getAttachConfig()->global->avatar->default_url;
		}

		return self::$parsed[$data['uid']];
	}
	
	/**
	 * 删除头像
	 * 
	 * @return bool
	 */
	public function delete()
	{
		$result = false;
		$this->getUserInfo();
		
		if ($this->userInfo['avatar']) {
			$data = $this->parse();
			
			if (file_exists($data['save_path']) && $data['save_path']!=self::$attachConfig->global->avatar->default_save_path) {
				unlink($data['save_path']);
			}
			
			if (file_exists($data['huge_save_path']) && $data['huge_save_path']!=self::$attachConfig->global->avatar->default_save_path) {
				unlink($data['huge_save_path']);
			}			
			
			if (file_exists($data['thumb_save_path']) && $data['thumb_save_path']!=self::$attachConfig->global->avatar->default_save_path) {
				unlink($data['thumb_save_path']);	
			}
			
			if (file_exists($data['tiny_save_path']) && $data['tiny_save_path']!=self::$attachConfig->global->avatar->default_save_path) {
				unlink($data['tiny_save_path']);	
			}
			
			Better_DAO_AttachAssign::getInstance()->delAssign($this->userInfo['avatar']);
			Better_DAO_Attachment::getInstance($this->uid)->deleteByCond(array(
				'uid' => $this->uid,
				'file_id' => $this->userInfo['avatar']
				));
				
			Better_User::getInstance($this->uid)->updateUser(array(
				'avatar' => '',
				));
			Better_Cache::remote()->set('kai_user_avatar_'.$this->uid, null);
			
			Better_DAO_Newimg::getInstance()->deleteByCond(array(
													'refid' =>$this->userInfo['avatar'],
													'type' => 'avatar'));
		}
		
		$result = true;
		
		return $result;
	}
}