<?php
require_once('../../settingan/ketetapan.php');
$screen_name=$_GET['screen_name'];
require(LIB_PATH.'oauth_twitter/config.php');
require(LIB_PATH.'oauth_twitter/oauth_lib.php');
$connection = get_connection(array('user_token'=>$_SESSION['oauth_token'],'user_secret'=>$_SESSION['oauth_token_secret']));
$connection->request('POST', $connection->url('1.1/blocks/create'),
  array(
    'screen_name'=>$screen_name,
    'include_entities'=>false,
    'skip_status'=>1
  )
);
$http_code = $connection->response['code'];
if ($http_code == 200 || $http_code == 403) { //berhasil block
  $connection->request('POST', $connection->url('1.1/blocks/destroy'),
  array(
    'screen_name'=>$screen_name,
    'include_entities'=>false,
    'skip_status'=>1
  )
);
  $http_code = $connection->response['code'];
  if ($http_code == 200 || $http_code == 403) { //berhasil unblock
    echo true;
  }
}
//~ elseif ($http_code == 403) { //sudah teman
  //~ echo true; //sementara di true, tapi bisa buat kondisi baru
//~ }
else {
  echo false;
}
?>