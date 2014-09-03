<?php
/**
 * 绑定贝多帐号
 *
 * @package Better.Service
 * @author zhoul <zhoul@peptalk.cn>
 *
 */

class Better_Service_BedoBinding
{
	private static $instance = null;
	
	public function __construct()
	{
		
	}
	
	
	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	} 
	
	public function login($username, $password)
	{
		$info = $this->userinfo($username);
		if (md5($password) == $info['password']) {
			if ($info['uid']) {
				$kaiUser = Better_DAO_User::getInstance()->getByUid($info['uid']);
				$info['kaiPass'] = $kaiUser['password'];
				$info['kaiEmail'] = $kaiUser['email'];
				return $info;
			} else {
				return $info;
			}
		} else {
			return false;
		}
	}
	
	public function userinfo($jid)
	{
		return Better_DAO_Bedo::getInstance()->userinfo($jid);
	}
	
	public function binding($uid, $jid, $syncTime)
	{
		Better_DAO_Bedo::getInstance()->setSyncInfo($uid, $jid, $syncTime);
		
		//添加好友
		$thisUser = Better_User::getInstance($uid);
		$thisUserInfo = $thisUser->getUser();
		
		$friends = Better_DAO_Bedo::getInstance()->getKaiFriends($jid);
		$bedoInfo = Better_DAO_Bedo::getInstance()->userinfo($jid);
		$bedoNickname = trim($bedoInfo['username']) != '' ? trim($bedoInfo['username']) : $jid;
		if ($friends) {
			foreach($friends as $val) {
				$result = Better_User_Friends::getInstance($uid)->agree($val['uid'], true);
				$codes = &$result['codes'];
				if ($result['code'] == $codes['SUCCESS']) {
					$bedoimport_notice_friend = str_replace('{bedo}',$bedoNickname,Better_Language::load()->global->bedoimport_friend);						
					$bedoimport_notice_friend = str_replace('{kai}',$thisUserInfo['nickname'],$bedoimport_notice_friend);
					Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
						'content' => $bedoimport_notice_friend,
						'receiver' => $val['uid']
						));
				}
			}
		}
		//转换积分到Karma
		$rp = $thisUserInfo['rp'];
		$bedoSorce = Better_DAO_Bedo::getInstance()->getSorce($jid);
		$newRp = $rp + intval($bedoSorce / 100);
		try{
			Better_Hook::factory(array(
				'Badge'
			))->invoke('NewSyncSites', array(
				'protocol' => 'bedo.cn',
				'uid' => $uid,
			));
		} catch(Exception $bb){
			Better_Log::getInstance()->logInfo($uid,'bedobadge');
		}
		$thisUser->updateUser(array(
	    		'rp' => $newRp,
	    		), true);
		return 'ok';
	}
	
	public function setBind($uid, $status)
	{
		Better_DAO_Bedo::getInstance()->setSyncContent($uid, $status);
		return;
	}
	
	public function getBindUser()
	{
		return Better_DAO_Bedo::getInstance()->getSyncUsers();
	}
	public function import($user)
	{
		return Better_DAO_Bedo::getInstance()->import($user);
	}
	
	public function getBindInfo($uid)
	{
		return Better_DAO_Bedo::getInstance()->getBedoUid($uid);
	}
	
	public function bind($uid, $jid, $password)
	{
		$bindInfo = $this->getBindInfo($uid);
		if ($bindInfo['bedoUid']) {
			return 'binded';
		}
		$bedoInfo = $this->userinfo($jid);		
		if (!$bedoInfo) {
			return 'none';
		} else if ($bedoInfo['uid']) {
			return 'bedobinded';
		} else if ($bedoInfo['password'] != md5($password)) {
			return 'errpass';
		} else {
			return $this->binding($uid, $jid, time());
			return 'ok';
		}
	}
	
	/**
	 * 同步头像、性别、生日、省份、城市等信息
	 */
	public function syncInfo($jid)
	{
		$userinfo = Better_DAO_Bedo::getInstance()->userinfo($jid);
		$thisUser = Better_User::getInstance($userinfo['uid']);
		//头像
		if ($userinfo['icon']) {
			$url = Better_Config::getAppConfig()->bedo->avatar_url . 'large/' . $userinfo['icon'];
			Better_User_Avatar::getInstance($userinfo['uid'])->upload($url, '', $userinfo['uid']);
		}
		$updateInfo = array();
		//生日
		if ($userinfo['bday']) {
			$updateInfo['birthday'] = $userinfo['bday'];
		}
		//性别
		if ($userinfo['gender']) {
			$gender = $userinfo['gender'] == 1 ? 'male' : ($userinfo['gender'] == 2 ? 'female' : 'secret');
			$updateInfo['gender'] = $gender;
		}
		//省份
		if ($userinfo['province']) {
			$provinceData = Better_Service_Geoname::bedoProvince($userinfo['province']);
			//城市
			if ($userinfo['city']) {
				$updateInfo['live_city'] = $userinfo['city'];
			}
			$updateInfo['live_province'] = $provinceData['1'];
		}
		
		if (count($updateInfo) > 0) {
			$thisUser->updateUser($updateInfo, true);
		}
		return;
	}
	
	public static function formatContent($content)
	{
		
		/*$patterns = array();
				$patterns[0] = '/\[\w*=*#*[\d,\w]*\]/';
				$patterns[1] = '/\[\/\w*\]/';
				$patterns[2] = '/\[\w*={1}\W*\]/';
				$patterns[3] = '/\[\w*=(http|ftp|https):\/\/[\w]+(.[\w]+)([\w\-\.,@?^=%&amp;:~\+#]*[\w\-\@?^=%&amp;~\+#])(\/\w*)*\.*\w*\]/';
				
				$replacements = array();
				$replacements[0] = '';
				$replacements[1] = '';
				$replacements[2] = '';
				$replacements[3] = '';
				
				$content = preg_replace($patterns, $replacements, $this->content);
				*/
		
		$sArr = array("'&#46;'","'<script[^>]*?>.*?</script>'","'(<br/>|</p>)'",
								"'<[^a]*?>'","'<\/[^a]*?>'","'&(quot|#34);'i","'&(amp|#38);'i","'&(lt|#60);'i",
                 "'&(gt|#62);'i","'&(nbsp|#160);'i","'&(iexcl|#161);'i","'&(cent|#162);'i","'&(pound|#163);'i",
                 "'&(copy|#169);'i","'&#(\d+);'e");
		$rArr = array(".","","\n\r","","", "\"","&","<",">"," ",
									chr(161),chr(162),chr(163),chr(169),"chr(\\1)");
		$content = preg_replace($sArr,$rArr,$content);	
		//匹配一个链接地址
		$i = preg_match_all("/<a href=[\'|\"]http:\/\/www\.(?!bedo\.cn\/mobile)[^>]*>(.*?)<\/a>/i", $content,$r);
		//匹配方括号模式
		$i = preg_match_all("/\[url\=http\:\/\/www\.(?!bedo.cn\/mobile)[^\]]*\](.*?)\[\/url\]/i", $content, $r2);	
		
		$content = self::__formatStr($r,$content);
		$content = self::__formatStr($r2,$content);	
		$i = preg_match_all("/\[url\=(http\:\/\/www\.(?=bedo\.cn\/mobile)[^\]]*.*?)[^\]]*\]/i", $content, $r3);
		$content = self::__formatStr($r3,$content,"<a href=",">");
		$content = str_replace("[/url]","</a>",$content);		
		
		$content = preg_replace("[\[(.*?)\]]", "", $content);
		return $content;
	}
	
	private static function __formatStr($r,$content,$a1='',$a2='')
	{
		$m1 = count($r);
		for($i=0;$i<$m1;$i++)
		{	$a = $r[$i];	
			for($j=0,$max=count($a);$j<$max;$j++)
			{	if($i<=$m1-2)
				{	//不符要求的链接将其去除(用文本替换整个标记)
					$content=str_replace($r[$i][$j],$a1.$r[$i+1][$j].$a2,$content);	
				}
			}
		}
		return $content;
	}
	
	public function getValidInfo($jid, $password, $username)
	{
		$patInvalid = '/([\s\r\t ])/is';
		$username = preg_replace($patInvalid, '', $username);

		if (mb_strlen($password, 'UTF-8') > 20){
			$password = mb_substr($password, 0, 8);
			$changePassword = $password;
		} else if (mb_strlen($password, 'UTF-8') < 6) {
			$password = rand(10000000,99999999);
			$changePassword = $password;
		}
		$email = $jid . '@bedo.cn';
		
		for($i = 0;$i < 10; $i++) {
			$r = Better_User_Validator::email($email, 0);
			if($r['code'] == 1) {
				$post['email'] = $email;
				break;
			} else {
				$email = $jid . rand(0, intval($jid)) . '@bedo.cn';
				$changeEmail = $email;
				$i = $i > 9 ? 0 : $i;
				continue;
			}
		}
		
		$nickname = str_replace(' ', '', $username);
		$nickname = str_replace('　', '', $nickname);
		
		$patAT = '/@/is';
		$patQuote = '/(")/';
		$patQuote2 = "/(')/";
		if (preg_match($patAT, $nickname) || preg_match($patQuote, $nickname) || preg_match($patQuote2, $nickname))
		{
			$patterns[0] = "/@/";
			$patterns[1] = "/'/";
			$patterns[2] = '/"/';
			$patarray[3] = '/(:)/';
			$patarray[4] = '/(,)/';
			$patarray[5] = '/(，)/';
			$patarray[6] = '/(：)/';
			$patarray[7] = '/(、)/';

			$replacements[7] = "";
			$replacements[6] = "";			
			$replacements[5] = "";
			$replacements[4] = "";
			$replacements[3] = "";			
			$replacements[2] = "";
			$replacements[1] = "";
			$replacements[0] = "";

			$nickname = preg_replace($patterns, $replacements, $nickname);
			$changeNickname = $nickname;
		}
		
		if (strlen($nickname) > 20) {
			$nickname =  mb_substr($nickname, 0, 6, 'UTF-8');
			$changeNickname = $nickname;
		}
		for($i = 0;$i < 10; $i++) {
			$r = Better_User_Validator::nickname($nickname, 0);
			if ($r['code'] == 1) {
				$username = $nickname;
				break;
			} else {
				$nickname = mb_substr($nickname, 0, 3, 'UTF-8');
				$nickname .= rand(0, intval($jid));
				$changeNickname = $nickname;
				$i = $i > 9 ? 0 : $i;
				continue;
			}
		}
		return array('email'=>$email, 'password' =>$password, 'username'=>$username,'changeNickname'=>$changeNickname, 'changePassword'=>$changePassword);
	}
}
