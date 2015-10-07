<?php
$screen_name = $_GET['screen_name'];
require('../oauth_twitter/config.php');
require('../oauth_twitter/oauth_twitter/oauth_lib.php');
$connection = get_connection( array('user_token' => ( isset($_SESSION['oauth_token']) ? $_SESSION['oauth_token'] : $_GET['oauth_token'] ),'user_secret'=> ( isset($_SESSION['oauth_token_secret']) ? $_SESSION['oauth_token_secret'] : $_GET['oauth_token_secret'] ) ));
$connection->request('POST', $connection->url('1.1/friendships/create'),
  array(
    'screen_name'=>$screen_name,
    'follow'=>true
  )
);
$http_code = $connection->response['code'];
if ($http_code == 200) { //berhasil
	/*
	if (isset($_GET['follow_us'])) {
		//automatic follow
		$tokens_us = q("SELECT token, token_secret FROM ref_account WHERE twitter_screenname = '" . get_config('twitter_product') . "' ")[0];
		$connection2 = get_connection( array('user_token' => $tokens_us['token'],'user_secret'=> $tokens_us['token_secret'] ));
		$connection2->request('POST', $connection->url('1.1/friendships/create'),
		  array(
			'screen_name'=> $_GET['client_screenname'],
			'follow'=>true
		  )
		);
		$http_code = $connection2->response['code'];		
		//----------------
	}
	 */
	
  echo true;
}
elseif ($http_code == 403) { //sudah teman
  echo true; //sementara di true, tapi bisa buat kondisi baru
}
else {
  echo false;
}
?>
