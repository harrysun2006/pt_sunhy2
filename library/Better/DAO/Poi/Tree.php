<?php
/* Poi 层次结构（连锁店）信息。*/

class Better_DAO_Poi_Tree extends Better_DAO_Poi_Base
{
  private static $instance = null;

  public function __construct($identifier = 0)
  {
    parent::__construct ($identifier);
    $this->tbl = BETTER_DB_TBL_PREFIX.'poi_tree';
    $this->priKey = 'pid';
  }

  public static function getInstance($identifier=0)
  {
    if (self::$instance==null) {
      self::$instance = new self();
      $db = parent::registerDbConnection('poi_server');
      self::$instance->_setAdapter($db);	
      self::$instance->setDb($db);
    }

    return self::$instance;
  }

  /* return 0 if parent doesn't exist.*/
  public function getParent($pid)
  {
    return 0;
  }
  
  public function getParentPois($page, $page_size)
  {
    $sql = sprintf("select pid,name from better_poi_tree t1 left join better_poi t2 on (t1.pid=t2.poi_id) where abstract=1 order by convert(name using gbk) limit %d,%d",
      ($page - 1) * $page_size, $page_size);
    $rows = $this->db->fetchAll($sql);
    $count = $this->db->fetchOne("select count(pid) from better_poi_tree where abstract=1");
    return array('rows'=>$rows,'count'=>$count);
  }

  public function getChildrenPois($parent_pid, $page, $page_size)
  {
    $sql = sprintf("select pid,name,city,address from better_poi_tree t1 left join better_poi t2 on (t1.pid=t2.poi_id) where parent_pid=$parent_pid limit %d,%d",
      ($page - 1) * $page_size, $page_size);
    $rows = $this->db->fetchAll($sql);
    $count = $this->db->fetchOne("select count(pid) from better_poi_tree where parent_pid=$parent_pid");
    return array('rows'=>$rows,'count'=>$count);
  }

  // $fields: [0] = name, [1..n] = search words, search words are optional.
  // return number of affected children pois.
  public function addParentPoi($fields)
  {
    if(!is_array($fields) || count($fields) < 1)
      return -1;

    $pid = $this->getAbstractPoiByName($fields[0]);
    if(!$pid)
      return -1;

    if(count($fields) == 1)
      $fields[1] = $fields[0];

    $n = 0;
    for($i = 1; $i < count($fields); ++$i)
    {
      $children = Better_DAO_Poi_Tree::searchByName($fields[$i]);
      if(!$children) continue;
      foreach($children as $pid2)
      {
        $this->setParent($pid2, $pid);
        ++$n;
      }
    }
    return $n;
  }

  public function delAbstractPoi($pid)
  {
    $this->db->delete('better_poi', "poi_id=$pid");
    $this->db->delete('better_poi_tree', "pid=$pid");
    return $this->db->update('better_poi_tree', array('parent_pid'=>0), "parent_pid=$pid");
  }

  // 清除 POI 的 parent_id 属性。
  public function unsetParent($pid)
  {
    return $this->db->update('better_poi_tree', array('parent_pid'=>0), "pid=$pid");
  }

  // return pid.
  // will create if not exists
  function getAbstractPoiByName($name)
  {
    $sql = "select pid from better_poi_tree t1 left join better_poi t2 on (t1.pid = t2.poi_id) where abstract=1 and name='$name'";
    $pid = $this->db->fetchOne($sql);
    if($pid)
    {
      return $pid;
    }
    else
    {
      // create poi
      $this->db->insert("better_poi", array(
        'poi_id' =>                 null,                       
        'city' =>                   '(多个)',                   
        'category_id' =>            0,                          
        'name' =>                   "$name",                    
        'xy' =>                     new Zend_Db_Expr("geomfromtext('point(0 0)')"), 
        'star' =>                   0,                            
        'score' =>                  0,                            
        'price' =>                  0,                            
        'address' =>                '(多个)',                     
        'phone' =>                  '',                           
        'label' =>                  '',                           
        'link' =>                   '',                           
        'logo' =>                   '',                           
        'intro' =>                  '',                           
        'country' =>                '',                           
        'province' =>               '',                           
        'creator' =>                175663,                       
        'major' =>                  0,                            
        'major_change_time' =>      0,                            
        'create_time' =>            new Zend_Db_Expr("unix_timestamp(now())"),        
        'checkins' =>               0,                            
        'favorites' =>              0,                            
        'users' =>                  0,                            
        'certified' =>              0,                            
        'visitors' =>               0,                            
        'posts' =>                  0,                            
        'tips' =>                   0,                            
        'aibang_id' =>              '',                           
        'ref_id' =>                 '',                           
        'closed' =>                 1,                            
        'last_update' =>            new Zend_Db_Expr("unix_timestamp(now())"),        
        'ownerid' =>                0,                            
        'cid' =>                    0,                            
        'region' =>                 '',                           
        'forbid_major' =>           0,                            
        'level' =>                  99,                           
        'level_b' =>                0,                            
        'level_r' =>                0,                            
        'level_adjust' =>           0                             
      ));
      $pid = $this->db->lastInsertId();
      $this->db->insert("better_poi_tree", array('pid'=>$pid, 'abstract'=>1, 'parent_pid'=>0));
      return $pid;
    }
  }

  function setParent($child, $parent)
  {
    $child = intval($child);
    $parent = intval($parent);
    if(!is_numeric($child) || !is_numeric($parent) || $child < 1 || $parent < 1)
      throw new Exception("bad arguments: setParent($child,$parent)");

    //print("set $child as one of $parent\n");
    $this->db->query("insert into better_poi_tree (pid,abstract,parent_pid) values ($child,0,$parent) on duplicate key update abstract=0,parent_pid=$parent");
  }

  /*
   * return pid array or null.
   *
   <?xml version="1.0" encoding="UTF-8"?>
   <response>
   <lst name="responseHeader"><int name="status">0</int><int name="QTime">28</int><lst name="params"><str name="q">{!lucene}name_norm:七斗星酒店</str><str name="rows">10</str></lst></lst><result name="response" numFound="1" start="0"><doc><str name="address">广东省深圳市福田区</str><int name="bonus">0</int><int name="category_id">1</int><str name="city">深圳市</str><double name="lat">22.51766</double><int name="level">20</int><double name="lon">114.04398</double><int name="major">0</int><str name="name">七斗星酒店</str><str name="name_chain"/><str name="name_norm">七斗星酒店</str><str name="phone">0755-83167777</str><int name="poi_id">4681544</int><double name="radius">1.0E-5</double></doc></result>
   </response>
   */
  static function searchByName($name)
  {
    $txt = file_get_contents("http://221.224.52.81:8983/solr/poi_chain/select?q={!lucene}name_norm:$name&rows=100000");
    $xml = simplexml_load_string($txt);
    $result = $xml->xpath('/response/result/doc/int[@name="poi_id"]');
    if(!$result) return null;
    $pids = array();
    while(list( , $pid) = each($result)) {
      $pids[] = $pid;
    }
    return $pids;
  }
}

