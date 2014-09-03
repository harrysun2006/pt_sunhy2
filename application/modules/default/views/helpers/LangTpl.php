<?php
/**
 * 在视图中输出字符较多的语言字符
 *
 * @author leip <leip@peptalk.cn>
 * @version 
 */

/**
 * LangTpl helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_LangTpl {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function LangTpl($key) 
	{
		$language = Better_Registry::get('language');
		!$language && $language = (string)Better_Config::getAppConfig()->language->default;
		$path = APPLICATION_PATH.'/configs/language/'.$language.'/'.$key.'.phtml';
		
		$lang = '';
		try {
			$lang = file_get_contents($path);
			$lang = str_replace('{BETTER_BASE_URL}', BETTER_BASE_URL, $lang);
			$lang = str_replace('{USERNAME}', $this->view->user['username'], $lang);
			$lang = str_replace('{NICKNAME}', $this->view->user['nickname'], $lang);
			$lang = str_replace('{EMAIL}', $this->view->user['email'], $lang);
		} catch (Exception $e) {
			
		}
		
		return $lang;
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
