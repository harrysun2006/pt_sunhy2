<?php
class HappybirthdayController extends Better_Controller_Front
{
	protected $dispUser = null;
	protected $dispUserInfo = array();
	
	public function init()
	{
		date_default_timezone_set("Asia/Shanghai");
		parent::init();
		$this->_helper->getHelper('Redirector')->gotoUrl('/home');
		$this->commonMeta();
		if (!$this->uid) {
			$this->_helper->getHelper('Redirector')->gotoUrl('/login');
			exit();
		}
	}
	
	
	public function indexAction()
	{
		$this->view->attrImg = $this->makeImg();
		$badges = $this->user->badge()->getMyBadges();
		$this->view->badges = $badges;
		$this->view->userInfo = $this->userInfo;
		$tip = '我正在签到我的城市，你呢~';
		$tip = $this->userInfo['rp'] >= 4000 ? '签到达人就是我啦！' : $tip;
		$tip = $this->userInfo['rp'] >= 1000 ? '嘿，我真是个签到控。不管你信不信，反正我信了~' : $tip;
		$tip = $this->userInfo['rp'] >= 500 ? '开开签到我玩的转！' : $tip;
		
		$checkIn = intval($this->userInfo['checkins']);
		$shout = intval($this->userInfo['now_posts']);
		$tips = intval($this->userInfo['now_tips']);
		$major = intval($this->userInfo['majors']);
		$cntBadges = count($badges);
		$content = "开开一周岁啦！与大家分享我在开开记录的#我的足迹#！一年来我签到{$checkIn}个地点，发表了{$tips}条贴士，成为{$major}个地点的掌门，还得到了{$cntBadges}枚勋章！{$tip}邀请你来开开一起来玩签到、享优惠，探索我们的城市！";
		$shout = "#祝福开开一周年#";
		$this->view->syncContent = $content;
		$this->view->shoutContent = $shout;
		$this->view->leftNum = 140 - mb_strlen($shout, 'UTF-8');
//		print_r($this->userInfo);exit;
	}
	
	public function testAction()
	{
		header('Content-type: image/png');
		$i = $this->makeImg();
		$img = new Imagick($i );
		echo $img;exit;
		/* Output the image with headers */
		echo $img;
		exit;
	}
	
	private function makeImg()
	{
		$publicPath = dirname(__FILE__) . '/../../../../public/';
		$checkIn = intval($this->userInfo['checkins']);
		$shout = intval($this->userInfo['now_posts']);
		$tips = intval($this->userInfo['now_tips']);
		$major = intval($this->userInfo['majors']);
		$fileName = $this->userInfo['uid'].'-' . $checkIn . '-' . $shout . '-' . $tips . '-' . $major . '.jpg';
		
///home/zl/peptalk/web/better/dev/line4/public/files/happybirthday		
		$path = $publicPath . 'files/happybirthday/';
		if (file_exists($path . $fileName)) {
			return 'http://'.$_SERVER['HTTP_HOST'] . '/files/happybirthday/'.$fileName;
		} 
		$blackBg = new ImagickPixel('black');
		$blackBg->clear();
		$img = new Imagick();
		
		$badges = $this->user->badge()->getMyBadges();
		$width = 464;
		$height = 196 + ceil(count($badges) / 5) * 80 + 8;
		
		$img->newImage($width, $height,$blackBg);
		$img->setImageFormat('jpg');
		
		$head = new Imagick($path.'pic.png');
		$bg = new Imagick($path.'bg.png');
		$img->compositeImage($head, $head->getImageCompose(), 2, 2);
		$img->compositeImage($bg, $bg->getImageCompose(), 2, 190);
		$draw = new ImagickDraw();
		$draw->setFillColor('#F3497F');
		$fonts = explode('|', Better_Config::getAppConfig()->scode->fonts);
		$draw->setFont($fonts[rand(0, count($fonts)-1)]);
		$draw->setFontSize(18);
		$img->annotateImage($draw, 40 - strlen($checkIn) * 5, 143, 0, $checkIn);
		$img->annotateImage($draw, 150 - strlen($shout) * 5, 143, 0, $shout);
		$img->annotateImage($draw, 275 - strlen($tips) * 5, 143, 0, $tips);
		$img->annotateImage($draw, 400 - strlen($major) * 5, 143, 0, $major);
		
		$i = 0;
		foreach($badges as $badge) {
			$row = intval($i / 5);
			$column = $i - $row * 5;
			$offsetX = $column * (72 + 16) + 16;
			$offsetY = $row * (72 + 8) + 8 + 192;
			$badgePath = $publicPath . 'images/badges/96/'. $badge['id'] . '.png';
			$t = new Imagick($badgePath);
			$t->adaptiveResizeImage(72, 72);
			$img->compositeImage($t, $t->getImageCompose(), $offsetX, $offsetY);
			$i++;
		}
	    $img->setImageCompression(Imagick::COMPRESSION_JPEG); 
	    $img->setimagecompressionquality(90);
		$img->writeImage($path . $fileName);
		return 'http://'.$_SERVER['HTTP_HOST'] . '/files/happybirthday/'.$fileName;
	}
	
	public function giftAction()
	{
		if (time() > strtotime('2011-09-01 00:00:00')) {
			$r = '你来晚了，活动已经结束啦！';
			$code = 0;
			echo json_encode(array('tip'=>$r, 'code'=>$code));
			exit;
		}
		$gift = $_POST['gift'];
		if ($gift != 'cloth' && $gift != 'badge') {
			$r = '礼物错误，我们没有这个礼物。';
		} else {
			$maxCloth = 30;
			$maxBadge = 50;
			$inviteNum = intval($this->userInfo['invites']);
			$logPath = dirname(__FILE__) . '/../../../../logs/';
			$logPath = '/home/zl/';
			$logFile = $logPath . 'happybirthdayGift.log';
			$dataFile = $logPath . 'happybirthdayData.log';
			$handle = @fopen($logFile, 'a');
			$prizeData = unserialize(file_get_contents($dataFile));
			$code = 0;
			if ($prizeData['cloth'][$this->uid] || $prizeData['badge'][$this->uid]){
				$r = '你已经申请过啦，每个人只能申请1个礼物。';
				if (!is_array($prizeData['cloth'][$this->uid]) && !is_array($prizeData['badge'][$this->uid])) {
					$name = $prizeData['cloth'][$this->uid] ? 'T恤' : '实物勋章';
					$r = '你已经申请过了，申请的礼品是' . $name . '，但是还没有填写收件信息，请填写';
					$code = 1;
				}
			} else if ($gift == 'cloth' && !$prizeData['cloth'][$this->uid] && count($prizeData['cloth']) >= $maxCloth || $gift == 50 && !$prizeData['badge'][$this->uid] && count($prizeData['badge']) >= $maxBadge) {
				$r = '你来晚了，礼物已经发完了。';
			} else if ($gift == 'cloth' && $inviteNum < 5 || $gift == 'badge' && $inviteNum < 3) {
				$r = '你还没有达到要求，努力加油吧。';
			} else {
				$prizeData[$gift][$this->uid] = 1;
				file_put_contents($dataFile, serialize($prizeData));
				$r = "申请成功，活动结束后我们开始发放礼品。\n\r你还需要填写联系方式";
				$code = 1;
			}
			if ($handle) {
				fwrite($handle, date('Y-m-d H:i:s') . "\t" . $this->uid . "\t" . $gift . "\t" . $r . "\n");
				fclose($handle);
			}
		}
		echo json_encode(array('tip'=>$r, 'code'=>$code));exit;
	}
	
	public function addressAction()
	{
		if (time() > strtotime('2011-09-01 00:00:00')) {
			$r = '你来晚了，活动已经结束啦！';
			$code = 0;
			echo json_encode(array('tip'=>$r, 'code'=>$code));
			exit;
		}
		$name = trim($_POST['name']);
		$address = trim($_POST['address']);
		$phone = trim($_POST['phone']);
		
		$logPath = dirname(__FILE__) . '/../../../../logs/';
		$logPath = '/home/zl/';
		$logFile = $logPath . 'happybirthdayGift.log';
		$dataFile = $logPath . 'happybirthdayData.log';
		$handle = @fopen($logFile, 'a');
		$prizeData = unserialize(file_get_contents($dataFile));
		$code = 0;
		if (!$name || !$address || !$phone)  {
			$r = '姓名、联系电话、收件地址不可以为空！';
		} else if (!$prizeData['cloth'][$this->uid] && !$prizeData['badge'][$this->uid]){
			$r = '你还没有申请过礼品，申请礼品后再来填写收件信息吧！';
			$code = 1;
		} else {
			$gift = $prizeData['cloth'][$this->uid] ? 'cloth' : 'badge';
			$prizeData[$gift][$this->uid] = array('name'=>$name, 'address'=>$address, 'phone'=>$phone);
			file_put_contents($dataFile, serialize($prizeData));
			$r = '我们已经记下你的地址，活动结束后开始发放礼品，感谢参与！';
			$code = 1;
		}
		if ($handle) {
			fwrite($handle, date('Y-m-d H:i:s') . "\t" . $this->uid . "\t" . $name . "\t" . $phone . "\t" . $address . "\t" . $r . "\n");
			fclose($handle);
		}
		echo json_encode(array('tip'=>$r, 'code'=>$code));exit;
	}
}