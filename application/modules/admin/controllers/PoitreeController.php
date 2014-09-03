<?php
/*
 * 这个管理员后台用于维护POI连锁店信息。
 */
class Admin_PoitreeController extends Better_Controller_Admin
{
	public function init()
	{
    //ini_set('display_errors', 1); // todo
		parent::init();
		$this->view->title="POI 连锁店信息";
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
    $this->view->page = isset($params['page']) ? (int)$params['page'] : 1;
    $this->view->page2 = isset($params['page2']) ? (int)$params['page2'] : 1;
    $this->view->page_size = 25;

		$this->view->parent_pois = Better_DAO_Poi_Tree::getInstance()->getParentPois($this->view->page, $this->view->page_size);
    $this->view->selected_parent_pid = -1;
    $this->view->children_pois = array('rows'=>array(), 'count'=>0);

    if(isset($params['pid']))
    {
      $this->view->selected_parent_pid = $params['pid'];
      $this->view->children_pois = Better_DAO_Poi_Tree::getInstance()->getChildrenPois($params['pid'], $this->view->page2, $this->view->page_size);
    }
	}

  public function addparentAction()
  {
    $lines = $this->getRequest()->getRawBody();
    $arr = explode("\n", $lines);
    foreach($arr as $line)
    {
      $line = trim($line);

      if(empty($line))
      {
        continue;
      }

      $fields = explode('|', $line);
      if(empty($fields) || empty($fields[0]))
      {
        print("err: 输入无效: $line\n");
        continue;
      }

      $n = Better_DAO_Poi_Tree::getInstance()->addParentPoi($fields);
      if($n < 0)
        print("err: 发生未知错误\n");
      else
        print("ok: 搜索到了 $n 个隶属于【{$fields[0]}】的 POI\n");
    }
  }

  public function delparentAction()
  {
    $pid = trim($this->getRequest()->getRawBody());
    if(!$pid)
    {
      print("err: 输入无效, 期望 \"parent_pid\"");
      return;
    }

    try
    {
      $n = Better_DAO_Poi_Tree::getInstance()->delAbstractPoi($pid);
      printf("ok: 释放了 $n 个POI");
    }
    catch(Exception $e)
    {
      printf("err: " . $e->getMessage());
    }
  }

  public function setparentAction()
  {
    $pids = trim($this->getRequest()->getRawBody());
    if(!$pids)
    {
      print("err: 输入无效, 期望 \"child_pid parent_pid\"");
      return;
    }

    $a = explode(' ', $pids);
    if(count($a) != 2 || !is_numeric($a[0]) || !is_numeric($a[1]))
    {
      print("err: 输入无效, 期望 \"child_pid parent_pid\"");
      return;
    }

    try
    {
      Better_DAO_Poi_Tree::getInstance()->setParent($a[0], $a[1]);
      printf("ok");
    }
    catch(Exception $e)
    {
      printf("err: " . $e->getMessage());
    }
  }

  // 清除 POI 的 parent_id 属性。
  public function unsetparentAction()
  {
    $pids = $this->getRequest()->getRawBody();
    if(!$pids)
    {
      print("err: 输入无效($pids)");
      return;
    }

    $nok = $nerr = 0;
    foreach(explode(' ', $pids) as $pid)
    {
      if(empty($pid)) continue;
      if(Better_DAO_Poi_Tree::getInstance()->unsetParent($pid) == 1)
        ++$nok;
      else
        ++$nerr;
    }
    if($nok > 0)
      printf("ok: $nok 已更新；$nerr 未更新");
    else
      printf("err: $nok 已更新；$nerr 未更新");
  }
}
?>
