<?php
header('Content-type: application/json');
set_time_limit (60);
require_once('_tmhoauth.php');
function tw_multi_conn($names) {
  $connection = new tmhOAuth();
  foreach($names as $name) {
    $connection->request('POST', $connection->url('1.1/friendships/create'), array('screen_name' => $name, 'follow' => true), true, false, array(), true);
  };
  return $connection->exec_multi();
}
//~ $connection = new tmhOAuth();
//~ $connection->request('POST', $connection->url('1.1/friendships/create'), array('screen_name' => 'vatih', 'follow' => true));
//~ echo '<pre>'.print_r($connection->response,1).'</pre>';exit();
$names = array('vatihsdad', 'vatih');
$resp = tw_multi_conn($names);
echo '<pre>'.print_r($resp,1).'</pre>';exit();



