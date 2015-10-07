<?php
require_once('../../settingan/ketetapan.php');
$screen_name=$_GET['screen_name'];
require(LIB_PATH.'oauth_twitter/config.php');
require(LIB_PATH.'oauth_twitter/oauth_lib.php');
$connection = get_connection(array('user_token'=>$_SESSION['oauth_token'],'user_secret'=>$_SESSION['oauth_token_secret']));
$connection->request('POST', $connection->url('1.1/friendships/destroy'),
  array(
    'screen_name'=>$screen_name
  )
);
$http_code = $connection->response['code'];
if ($http_code == 200) { //berhasil
  echo true;
}
elseif ($http_code == 403) { //sudah teman
  echo true; //sementara di true, tapi bisa buat kondisi baru
}
else {
  echo false;
}
?>