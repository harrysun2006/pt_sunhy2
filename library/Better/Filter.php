<?php

/**
 * 文字过滤接口
 *
 * @package Better
 * @author  leip <leip@peptalk.cn>
 *
 */
class Better_Filter
{

	private static $instance = null;
	private $FILTER_EXE = '/home/tools/filter/src/keywordfilter.py';
	protected $lastResult = 0;
	protected $lastWords = '';
	protected $lastId = 0;
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			self::$instance->FILTER_EXE = Better_Config::getAppConfig()->filter->exe;
		}

		return self::$instance;
	}
	
	/**
	 * 返回上一次过滤结果
	 * 
	 * @return bool
	 */
	public function getLastResult()
	{
		return $this->lastResult;
	}
	
	/**
	 * 返回上一次过滤的关键词
	 */
	public function getLastWords()
	{
		return $this->lastWords;
	}
	
	public function getLastId(){
		return $this->lastId;
	}
	
	
	
	private function highlight($str, $words) {
		
		if(is_array($words)){
			if($words){
				foreach($words as $val){
					$keyword = trim($val);
					if ($keyword) {
						$str = str_ireplace($keyword, '<span class="highlight">'.$keyword.'</span>', $str);
					}
				}
			}
		}else{
			$keyword = trim($words);
			if ($keyword) {
				$str = str_ireplace($keyword, '<span class="highlight">'.$keyword.'</span>', $str);
			}
		}
		
		return $str;
	}

	/**
	 * 进行过滤操作
	 *
	 * @param string $text 文字
	 * @param string $type 类型（消息、用户名、真实姓名等）
	 * @param string $key 数据主键id
	 * @return null
	 */
	public function filter($text, $type, $uid, $username, $bid='', $userinfo=array())
	{
		$this->lastResult = array();
		if ($text == '') {
			return $this->lastResult;
		}
		$flag = false;
		$word_type = array();
		$words = array();
		$result = true;
		$check_words = '';
		$need_check = 0;
		
		if($type=='direct_message'){
			$text = '发送给:'.$uid.'<br />'.$text;
		}
		
		$text = self::make_semiangle($text);
		
		$result = true;
		$file1 = file(Better_Config::getAppConfig()->filter->words2.'-1.txt');
		$file2 = file(Better_Config::getAppConfig()->filter->words2.'-2.txt');
		$file = array_merge($file1, $file2);
		foreach($file as $val){
			$val = trim($val);
			if($val){
				if(preg_match('/'.$val.'/i', $text)){
					$result = false;
					break;
				}
			}
		}
		
		if(!$result){
			$flag = true;
			$this->lastResult[] = 2;//2=>替换发表
			$word_type[] = '2';
			
				foreach($file as $val){
					$val = trim($val);
					if($type!='userinfo'){
						$text = str_ireplace($val, '***', $text);
					}
					$words[] = $val;
				}
		}
		
		list($result, $level, $word, $ref) = $this->wordFinterByPy($text, '3:word3-1.txt 3:word3-2.txt', 1);
		//特定的来源审核
		$is_ip = self::checkRegIp($userinfo['regip'], '南非');
		
		if(!$result || $is_ip){
			$flag = true;
			$this->lastResult[] = 3;//3=>审核发表
			$word_type[] = '3';
			$need_check = 1;
			if($check_words){
				$check_words .=','.$word;
			}else{
				$check_words .=$word;
			}
			
		}
		
		
		list($result, $level, $word, $ref) = $this->wordFinterByPy($text, '1:word1-1.txt,2:word2-1.txt,3:word3-1.txt,4:word4-1.txt 1:word1-2.txt,2:word2-2.txt,3:word3-2.txt,4:word4-2.txt', 0);
		if(!$result){
			$flag = true;
			$lev = explode(',', $level);
			foreach($lev as $val){
				if(!in_array($val, $word_type)){
					$word_type[] = $val;
				}
			}
			
			if($check_words){
				$check_words .=','.$word;
			}else{
				$check_words .=$word;
			}
		}
		
		
		if ($flag) {
			//list($title, $content) = explode('|', $text);
			$found_words = explode(',', $check_words);
			$found_words = array_unique($found_words);
			foreach ($found_words as $the_word) {
				list($to_find, $found) = explode('|', $the_word);
				if ($found == '') {
					$found = $to_find;
				}
				$cwords[] = $to_find;
				$replaces[] = $found;
			}
			$cwords = array_unique($cwords);
			$text = $this->highlight($text, array_merge($words, $replaces));
		
			$d = array();
			$d['bid'] = $bid;
			$d['uid'] = intval($uid);
			$d['refid'] = intval($bid);
			$d['type'] = $type;
			$d['url'] = '';
			$d['reftext'] = $text;
			$d['createtime'] = time();
			$d['flag'] = '1';
			$d['username'] = $username;
			$d['word_type'] = implode('|', $word_type);
			$d['check_words'] = implode(',', $cwords);
			$d['need_check'] = $need_check;
			
			$this->lastId = Better_DAO_Filter::getInstance()->insert($d);
			
		}
		
		return $this->lastResult;
	}
	
	/**
	 * 
	 * @param $ip
	 * @param $address
	 * @return unknown_type
	 */
	public static function checkRegIp($ip, $address)
	{
		$ip_int = sprintf("%u", ip2long($ip));
		$ipdata = Better_DAO_Ipdata::getInstance();
		$sql = " SELECT * FROM better_ipdata WHERE address='$address' AND ( $ip_int >= rstart AND $ip_int <= rend) LIMIT 1 ";
		$result = $ipdata->query($sql);
		$row = $result->fetchAll();
		return $row;
	}	
	
	/**
	 * 进行过滤禁止发言关键词，不进数据库
	 *
	 * @param string $text 文字
	 * 
	 */
	public function filterBanwords($text)
	{
		$this->lastResult = false;
		
		$text = self::make_semiangle($text);
		$file1 = file(Better_Config::getAppConfig()->filter->words1.'-1.txt');
		$file2 = file(Better_Config::getAppConfig()->filter->words1.'-2.txt');
		$file = array_merge($file1, $file2);
		foreach($file as $val){
			$val = trim($val);
			if($val){
				if(preg_match('/'.$val.'/i', $text)){
					$this->lastResult = true;
					break;
				}
			}
		}
		
		return $this->lastResult;
	}
	
	
	/**
	 * 进行过滤禁止创建的POI名称
	 * @param string $text 文字
	 */
	public static function filterPoiwords($text)
	{
		$result = false;
		$text = self::make_semiangle($text);
		$text = preg_replace("/\s/si", "", $text);
		$poi_words = file(Better_Config::getAppConfig()->filter->poi_words);
		foreach($poi_words as $val){
			$val = trim($val);
			if($val){
				if(preg_match('/'.$val.'/i', $text)){
					$result = true;
					break;
				}
			}
		}
		
		return $result;
	}
	
	
	public function filterNameBanwords($name)
	{
		$result = false;
		
		$name_words = array(
			'朱镕基','朱容基','朱德 ','周恩来','张丕林','曾庆红 ','杨尚昆 ','吴邦国','温家宝','尉健行','万里 ',
			'钱其琛','马英九 ','毛泽东','刘少奇','林彪','李先念 ','李瑞环','李鹏','李岚清','李洪志','李长春',
			'江泽民','江青','贾庆林','黄菊','胡锦涛','邓小平','陈独秀');
		
		foreach($name_words as $val){
			$val = trim($val);
			if($val){
				if(preg_match('/'.$val.'/i', $name)){
					$result = true;
					break;
				}
			}
		}
		
		return $result;
	}
	
	
	/**
	 * 执行外部python程序检查内容
	 * 
	 * @param $content
	 * $type:
	 * 1，精确
	 * 0，模糊
	 * @return array
	 */
	protected function wordFinterByPy($content, $filename, $type)
	{
		$content = self::make_semiangle($content);
		$content = str_replace('`', '\`', $content);
		$content = htmlspecialchars_decode($content);
		$content = preg_replace("/\s/si", " ", $content);
		$content = preg_replace('/\xa3([\xa1-\xfe])/e', 'chr(ord(\1)-0x80)', $content);
		$chk_content = iconv("UTF-8", "gbk", $content);
		$chk_content = str_replace("\'", '', $chk_content);
		$chk_content = str_replace("\"", '', $chk_content);
		try {
			//调用python程序检察文章内容\\n
			$rscnt = exec($this->FILTER_EXE . ' "' . $chk_content . '" '.$filename.' '.$type);
			list($result, $level, $word, $ref) = explode("\t", $rscnt);
			$result = $result!= "False";	
		} catch (Exception $e) {
			$level= $word = $ref = '';
			$result = true;
			
			Better_Log::getInstance()->logInfo($e->getTraceAsString(), 'filter_exception');
		}
		return array($result, $level, $word, $ref);
	}
	
	
  /**
   * 全角转半角后再过滤
   * @param unknown_type $str
   */
  public static function  make_semiangle($str)      
  {      
     $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',      
                  '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',      
                  'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',      
                  'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',      
                  'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',      
                  'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',      
                  'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',      
                  'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',      
                  'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',      
                  'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',      
                  'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',      
                  'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',      
                  'ｙ' => 'y', 'ｚ' => 'z',      
                  '（' => '(', '）' => ')', '【' => '[',      
                  '】' => ']', '〖' => '[', '〗' => ']',      
                  '｛' => '{', '｝' => '}',
                  '．'=>'.', '＿'=>'_',    
                  '％' => '%', '＋' => '+', '—' => '-', '－' => '-',      
                  '：' => ':',     
                  '？' => '?', '！' => '!', '…' => '-', 
                  '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"',      
                  '　' => ' ','＄'=>'$','＠'=>'@','＃'=>'#','＾'=>'^','＆'=>'&','＊'=>'*',   
                  '＂'=>'"');   
    
     return strtr($str, $arr);      
  }   


}