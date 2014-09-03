<?php
/*
 * 这个管理员后台用于 搜索、合并、修改 POI，尤其适合处理大体量 POI。
 */
class Admin_PoiclusterController extends Better_Controller_Admin
{
	public function init()
	{
    //ini_set('display_errors', 1); // todo
		parent::init();
		$this->view->title="POI 搜索/合并/编辑";

    // 添加 js, 需要在 view 中调用 $this->headScript();
		//$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/poicluster.js?ver='.BETTER_VER_CODE);
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		//Zend_Debug::dump($params);exit();
		$this->view->params = $params;

    //
    // 获取关键词列表
    // 支持分页
    $opts = array(
      'page'=>$params['page'],
      'page_size'=>isset($params['page_size']) ? $params['page_size'] : 35);
		$this->view->keywords = Better_DAO_Admin_Poicluster::getInstance()->getKeywords($opts);

    //
    // 获取POI列表
    // 不支持分页，但可设置显示数量
		$pois = array();
    // 来源A：搜索POI
    if($params['namekeyword'] || $params['placekeyword'])
    {
      $opts = $params;
      $opts['page'] = 1;
      $opts['page_size'] = isset($params['search_limit']) ? $params['search_limit'] : 60; 
			$search_result = Better_Admin_Poi::getPOIs($opts);

      // Better_Admin_Poi::getPOIs() 返回的 POI 信息可能缺少一些这里感兴趣的属性，
      // 所以要用 Better_Poi_Info 补充一下。
      //
      // 目前 POI 搜索服务没有返回半径，和 level_adjust
      foreach($search_result['rows'] as &$p)
      {
        $pois[] = Better_Poi_Info::getInstance($p['poi_id'], true)->getBasic();
      }
      unset($p);
		}
    // 来源B：用户通过 pid<pid> 参数指定
    foreach(array_keys($params) as $i)
    {
      if(preg_match('/^pid[0-9]+$/', $i) == 1)
      {
        $pid = substr($i, 3);
        $pois[] = Better_Poi_Info::getInstance($pid, true)->getBasic();
      }
    }

    // 替换已关闭POI的名称（不要显示为“(地球的某个角落)”）
    foreach($pois as &$p)
    {
      if($p['closed'] == 1)
      {
        $p['name'] = '(已关闭)';
      }
    }

    // 按名称拼音排序POI
    usort($pois, 'cmp_poi');

		//Zend_Debug::dump($params);exit();
		$this->view->params = $params;
		$this->view->pois = $pois;
	}

  /* 删除一个关键词 */
  public function delkeywordAction()
  {
		$params = $this->getRequest()->getParams();
    echo Better_DAO_Admin_Poicluster::getInstance()->delKeyword($params['w']);
  }

  /* for HTTP POST */
  public function updateAction()
  {
    $err_cnt = 0;
    $ok_cnt = 0;
    $resp = "";

    $rawbody = $this->getRequest()->getRawBody();
    $xml = simplexml_load_string($rawbody);
    if(!$xml)
    {
      $resp .= "无效的XML数据：\n$rawbody";
      ++$err_cnt;
      return;
    }

    // 执行合并
    foreach($xml->merge->children() as $item)
    {
      $attr = array();
      foreach($item->attributes() as $k=>$v)
        $attr[$k] = (string)$v;

      if(isset($attr['from']) && is_numeric($attr['from'])
        && isset($attr['from']) && is_numeric($attr['from']))
      {
        if(1 == Better_Admin_Simipoi::mergePOI($attr['from'], $attr['to']))
        {
          $resp .= sprintf("合并成功: %s => %s\n", $attr['from'], $attr['to']);
          ++$ok_cnt;
        }
        else
        {
          $resp .= sprintf("合并失败: %s => %s\n", $attr['from'], $attr['to']);
          ++$err_cnt;
        }
      }
    }

    // 执行对 better_poi 表的修改
    foreach($xml->basic->children() as $poi)
    {
      $pid = (string)$poi['id'];
      $attr = array();
      foreach($poi->children() as $item)
      {
        $key = $item->getName();
        $val = (string)$item;
        $attr[$key] = $val;
      }

      // convert lat,lon to x,y
      if(isset($attr['lat']) && isset($attr['lon']))
      {
        list($x,$y) = ll2xy($attr['lon'], $attr['lat']);
        $attr['x'] = $x;
        $attr['y'] = $y;
        unset($attr['lat']);
        unset($attr['lon']);
      }
      //$resp .= ("poi#$pid\n") . print_r($attr, true);

      if(Better_Admin_Poi::updatePOI($attr, $pid))
      {
        $resp .= ("成功更新 $pid 的普通属性\n");
        ++$ok_cnt;
      }
      else
      {
        $resp .= ("无法更新 $pid 的普通属性\n");
        ++$err_cnt;
      }
    }

    // 执行对 better_poi_extra 表的修改
    // 注意一个 POI 可能不在 better_poi_extra 表中，所以有时需要 insert 操作。
    foreach($xml->extra->children() as $poi)
    {
      $pid = (string)$poi['id'];
      $attr = array();
      foreach($poi->children() as $item)
      {
        $key = $item->getName();
        $val = (string)$item;
        $attr[$key] = $val;
      }
      //$resp .= ("poi#$pid\n"). print_r($attr, true);

      if(0 == Better_DAO_Poi_Extra::getInstance()->update($attr, $pid))
      {
        $attr['poi_id'] = $pid;
        if(0 == Better_DAO_Poi_Extra::getInstance()->insert($attr))
        {
          $resp .= ("无法添加 $pid 的扩展属性\n");
          ++$err_cnt;
        }
        else
        {
          // 通知POI搜索引擎有更新
          Better_DAO_Poi_Fulltext::getInstance()->updateItem($pid, 1);
          $resp .= ("成功添加 $pid 的扩展属性\n");
          ++$ok_cnt;
        }
      }
      else
      {
        // 通知POI搜索引擎有更新
        Better_DAO_Poi_Fulltext::getInstance()->updateItem($pid, 1);
        $resp .= ("成功更新 $pid 的扩展属性\n");
        ++$ok_cnt;
      }
    }
    if($err_cnt > 0)
      print("err：有些操作没有成功保存。\n");
    else
      print("ok\n");
    echo $resp;
  }
}

// 按名称拼音比较两个POI，用于排序
function cmp_poi($a, $b)
{
  return strcmp(iconv('UTF-8', 'GBK', $a['name']), iconv('UTF-8', 'GBK', $b['name']));
}

function ll2xy($lon, $lat)
{
  $PI = pi();
  $x = round($lon/360*256 * pow(2,17));
  $y = round(log(tan(($lat*$PI/180+$PI/2)/2))*256/$PI/2 * pow(2,17));
  return array($x, $y);
}

?>
