<?php
/**
 *
 * @author pysche
 * @version 
 */

/**
 * Paginator helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_Paginator {
	
	protected $paginator = '';
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function paginator($need_select=true) {
		if ($this->paginator=='') {
			$params = &$this->view->params;
			
			$page = $params['page'] ? intval($params['page']) : 1;
			$page<=0 && $page = 1;
			
			$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
			$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
			
			$count = $this->view->count;
			
			$tmp = $count/$pageSize;
			$pages = $tmp==intval($tmp) ? $tmp : ceil($tmp);
			
			unset($params['count']);
			// Refresh
			$this->paginator .= '[<a href="'.$this->view->url(array_merge($params, array( 'reload'=>0))).'">刷新页面</a>]';
			
			// Reload
			$this->paginator .= '&nbsp;[<a href="'.$this->view->url(array_merge($params, array( 'reload'=>1))).'">刷新缓存</a>]';
			//	First
			$this->paginator .= '&nbsp;<a href="'.$this->view->url(array_merge($params, array('page'=>1, 'reload'=>0))).'" >首页</a>';
			
			// Prev
			if ($page>1) {
				$this->paginator .= '&nbsp;<a href="'.$this->view->url(array_merge($params, array('page'=>($page-1), 'reload'=>0))).'">上一页</a>';
			} else {
				$this->paginator .= '&nbsp;<span class="empty_pager">上一页</span>';
			}
			
			// Next
			if ($page<$pages) {
				$this->paginator .= '&nbsp;<a href="'.$this->view->url(array_merge($params, array('page'=>($page+1), 'reload'=>0))).'">下一页</a>';
			} else {
				$this->paginator .= '&nbsp;<span class="empty_pager">下一页</span>';
			}
			
			// Last
			$this->paginator .= '&nbsp;<a href="'.$this->view->url(array_merge($params, array('page'=>$pages, 'reload'=>0))).'" >末页</a>';
			
			if($need_select){
				// Select
				$this->paginator .= '&nbsp;转到: <select >';
				for ($i=1;$i<=$pages;$i++) {
					$this->paginator .= '<option value="'.$i.'"';
					$this->paginator .= $i==$page ? ' selected="selected"' : '';
					$this->paginator .= '>第'.$i.'页</option>';
				}
				$this->paginator .= '</select>';
			}
			
			//	Jump
			$this->paginator .= '&nbsp;跳转:';
			$this->paginator .= '<input type="text" size="3" class="input" name="to_page" id="to_page" value="'.$page.'" />';
			$this->paginator .= ' <input type="button" value="Go" id="btnPageJump" class="btnPageJump"/>';
			
			$this->paginator .= '&nbsp;共有 <span class="error">'.$count.'</span> 条数据, 分为 <span class="error">'.$pages.'</span> 页, 每页 <span class="error">'.$pageSize.'</span> 行, 当前页: <span class="error">'.$page.'</span>';
		
		}

		return $this->paginator;
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}

}
