<?php
//header('Content-type: application/json');
$status = $_GET['status'];
  require(LIB_PATH.'oauth_twitter/config.php');
  require(LIB_PATH.'oauth_twitter/oauth_lib.php');
  $connection = get_connection(array('user_token'=>$_SESSION['oauth_token'],'user_secret'=>$_SESSION['oauth_token_secret']));
  $connection->request('POST', $connection->url('1.1/statuses/update.json'),
    array(
      'status' => $status
    )
  );
  $http_code = $connection->response['code'];
  if ($http_code == 200) {
    $user = 'ok';
  } else {
    $user = $http_code;
  }
  die( json_encode( array('status' => $user) ) );
