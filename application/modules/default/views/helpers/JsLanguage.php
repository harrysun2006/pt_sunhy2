<?php
/**
 * 在视图中输出语言相关的javascript
 *
 * @author leip
 * @version 
 */

/**
 * JsLanguage helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_JsLanguage {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function jsLanguage() {
		// TODO Auto-generated Zend_View_Helper_JsLanguage::jsLanguage() helper 
		Better_Language::load();
		$language = Better_Registry::get('language');

		if (file_exists(APPLICATION_PATH.'/../public/js/lang-'.$language.'.js')) {
			$js = '
<script type="text/javascript" language="javascript" src="/js/lang-'.$language.'.js?ver='.BETTER_VER_CODE.'" charset="utf-8"></script>
			';
		} else {
			$js = '
<script type="text/javascript" language="javascript">';
			
			foreach ($this->view->lang->javascript as $key=>$lang) {
				if (is_object($lang)) {
					$js .= 'betterLang.'.$key.' = new Object();
					';
					foreach ($lang as $k=>$v) {
						$js .= 'betterLang.'.$key.'.'.$k.'="'.$v.'";
						';
					}
				} else {
					$js .= 'betterLang.'.$key.' = "'.$lang.'";
					';
				}
			}
			
			$js .= '
</script>
			';
		}
		
		return $js;
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
