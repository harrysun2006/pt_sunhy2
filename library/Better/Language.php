<?php

/**
 * 语言包加载
 * 语言包文件对应在application/configs/language目录下，一个语言包对应一个ini文件，文件格式可参考application.ini
 *
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Language
{
	
	public static $supportedLanguage = array('zh-cn', 'en');
	
	public static function get()
	{
		return Better_Registry::get('lang');	
	}

	/**
	 * 加载语言包
	 *
	 * @return unknown_type
	 */
	public static function load($default='')
	{
		static $loaded = false ;

		if (!$loaded) {
			if ($default=='') {
				$language = $_COOKIE['lan'];
				
				if($language) {
					if ($language!='en') {
						$language = 'zh-cn';
					}
				} else {
					//先根据浏览器语言判断是否有对应语言包
					if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
						$httpLang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4));
						if (preg_match('/en/i', $httpLang)) {
							$language = 'en';
						} else {
							$language = 'zh-cn';
						}
					}
					
					$language || $language = (string)Better_Config::getAppConfig()->language->default;
				}
			} else {
				$language = $default;
			}

			$language = 'zh-cn';
			Better_Registry::set('language', $language);

			//读取语言包并加载到cache
			$cache = Better_Cache::local();
			$cacheKey = 'language'.str_replace('-', '', $language) . md5(APPLICATION_ENV);	//	$cache对象的键值只能是a-zA-Z0-9，所以删除这里的“-”符号
	
			if (!$cachedLang=$cache->load($cacheKey)) {
				$cachedLang = new Zend_Config_Ini(APPLICATION_PATH.'/configs/language/'.$language.'.ini');
				$cache->set($cacheKey, $cachedLang);
	
				$js = 'betterLang = '.json_encode($cachedLang->javascript->toArray());
				
				//	缓存javascript的语言设置到一个js文件
				if (!defined('IN_CRON')) { //命令行执行 得不到APC缓存 就不要重复创建文件了
					$jsFile = APPLICATION_PATH.'/../public/js/lang-'.$language.'.js';
					file_exists($jsFile) && chmod($jsFile, 0644);
					$fp = fopen($jsFile, 'w');
					fwrite($fp, $js);
					fclose($fp);
				}
			}
	
			Better_Registry::set('lang', $cachedLang);
			$loaded = true;
		} else {
			$language = Better_Registry::get('language');
			$cachedLang = Better_Cache::local()->get('language' . str_replace('-', '', $language) . md5(APPLICATION_ENV));
		}

		return $cachedLang;
	}
	
	public static function loadDbKey($key, &$data, $language='')
	{
		$language = Better_Registry::get('language');	
		$language = 'zh-cn';
		switch ($language) {
			case 'en':
				$result = isset($data['en_'.$key]) ? $data['en_'.$key] : $data[$key];
				break;
			default:
				$result = $data[$key];
				break;
		}
		
		return $result;
	}
	
	public static function loadIt($key)
	{
		if (preg_match('/^en(.*)/i', $key)) {
			$key = 'en';
		} else {
			$key = 'zh-cn';
		}
		$key = 'zh-cn';
		
		$cacher = Better_Cache::local();
		$cacheKey = 'language'.str_replace('-', '', $key);
		if (!$lang = $cacher->load($cacheKey)) {
			$lang = new Zend_Config_Ini(APPLICATION_PATH.'/configs/language/'.$key.'.ini');
			$cacher->set($cacheKey, $lang);
		}
		
		return $lang;
	}	

}

?>