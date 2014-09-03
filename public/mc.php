<?php
$action = $_REQUEST['action'];
$key = $_REQUEST['key'];
$key_md5 = md5($key);
$mc = new Memcache();
$mc->addServer('127.0.0.1',11211);
if ($action == 'get') {
  $value = $mc->get($key_md5);
  var_dump($key_md5);
  var_dump($value);
} else if ($action == 'delete') {
  echo $mc->delete($key_md5);
} else if ($action == 'md5') {
  var_dump($key_md5);
}

?>
