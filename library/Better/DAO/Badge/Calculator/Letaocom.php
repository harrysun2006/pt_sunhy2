<?php

/**
勋章名称
 〖乐淘〗
 
勋章分类
 品牌优惠
 
获得条件
 吼吼“我最爱手机乐淘”+以下任一关键词：

红鸟鞋、黄鸟鞋、黑鸟鞋、绿鸟鞋、蓝鸟鞋、白鸟鞋、绿猪鞋

如：我最爱手机乐淘红鸟鞋，即可获得勋章。
 
上线时间
 6月23日即时
 
下线时间
 7月7日12:00am
 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Letaocom extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 6, 21, 2011);
		$endtm = gmmktime(4, 0, 0, 7, 7, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm){			
			$blog = &$params['blog'];						
			if ($blog['type']=='normal') {
				$message = strtolower($blog['message']);
				$checked1 = '/我最爱手机乐淘/';					
				if (preg_match($checked1, $message)) {
									
					$checkinfo = array('/红鸟鞋/','/黄鸟鞋/','/黑鸟鞋/','/绿鸟鞋/','/蓝鸟鞋/','/白鸟鞋/','/绿猪鞋/');
					foreach($checkinfo as $row){
						
						if (preg_match($row, $message)){
							$result = true;	
							break;
						}	
					}
						
				}
			}			
		}	
		if($result)	{
			try{
				self::__sentMsg($uid);
			} catch (Exception $e){
				Better_Log::getInstance()->logInfo($uid,'notgotletaocode');
			}
		}	
		
		return $result;
	}
	
	public static function __sentMsg($uid)
	{
		
		$appConfig = Better_Config::getAppConfig();
		$sys_user_id = $appConfig->user->sys_user_id;
		$content = <<<EOT
嗨，亲爱的开饭，您获得了开开k.ai〖乐淘〗勋章！感谢您的参与，送您乐淘网10元电子抵用券，兑换码：CODE 本电子优惠券仅限于乐淘手机客户端使用。您可http://www.letao.com/mobile/下载乐淘手机客户端（支持iPhone/Android/Symbian），成功安装并下单，即可立减10元，使用本电子优惠券再减10元！祝您每日[开]心！		
EOT;
		
		$data = Better_DAO_Letao::getInstance()->get(array('uid' => 0));
		$id = $data['id'];
		if (!$id) return false;
		$content = str_replace('CODE', $id, $content);
		Better_User_Notification_DirectMessage::getInstance($sys_user_id)->send(array(
												'content' => $content,
												'receiver' => $uid
												));	
		$_data['uid'] = $uid;
		$_data['dateline'] = time();
		$a = Better_DAO_Letao::getInstance()->update($_data, $id);	

		return $a;
												
	}
	
	
}