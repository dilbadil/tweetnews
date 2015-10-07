<?php
session_start();
function token_request($callback_url) //step 1 == request generated token
{
	require_once('oauth_twitter/oauth_lib.php');
	//request oauth token , secret, callback url
	$connection = get_connection();
	$connection->request('POST', $connection->url('oauth/request_token', ''),
		array(
		  'oauth_callback' => $callback_url	//c_link("login/register/register")
		)
	);

	$dt['response'] = $connection->response['response'];
	if ($connection->response['response']=='') {
		$dt['error'] = $connection->response['error'];
		echo '<pre>'.print_r($connection->response['error'],1).'</pre>';
		exit();
	}

	$dt['status'] = $connection->response['headers']['status'];
	if ($dt['status'] == '200 OK') {
		$oauth = $connection->response['response'];
		parse_str($oauth);
		if ($oauth_callback_confirmed == true) {
			$dt['oauth_token_secret'] = $oauth_token_secret;
			$dt['oauth_token'] = $oauth_token;
		}
	}
	return $dt;
}

function token_verify($oauth = array())	//step 2 == check requested token from step 1. if true get data from twitter
{
	require_once('oauth_twitter/config.php');
	require_once('oauth_twitter/oauth_lib.php');
	$connection = get_connection();
	$connection->request('POST', $connection->url('oauth/access_token',''),
	array('oauth_verifier'=>$oauth['oauth_verifier'],
		'oauth_token'=>$oauth['oauth_token']
	));
	$dt['status'] = $connection->response['headers']['status'];
	if ($dt['status'] == '200 OK') {
		$oauths = $connection->response['response'];
		parse_str($oauths);
		$dt['oauth_token_secret'] = $oauth_token_secret;
		$dt['oauth_token'] = $oauth_token;
		$dt['user_id'] = $user_id;
		$dt['screen_name'] = $screen_name;
	} else {
		$dt['error'] = $connection->response['error'];
	}
	return $dt;
}

if ($_GET['nav'] == 'via_twitter') {	//step 1
	$callback_url = 'http://abdiid.com/scrape/ganon/twitter/login.php?nav=verifikasi';
	$oauth = token_request($callback_url);
	//echo "<pre>".print_r( $oauth,1 )."</pre>";
	if ($oauth['status'] == '200 OK') {
		$oauth_token = $oauth['oauth_token'];
		$_SESSION['oauth_token'] = $oauth_token;
	} else {
		echo $oauth['error']; exit;
	}

	if (isset($_GET['force_login'])) {
		header("location: http://twitter.com/oauth/authenticate?oauth_token=$oauth_token&force_login=true");
	} else {
		header("location: http://twitter.com/oauth/authenticate?oauth_token=$oauth_token");		
	}
	exit;
}
else if ($_GET['nav'] == 'verifikasi' && isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {	//step 2
	if ($_GET['oauth_token'] == $_SESSION['oauth_token']) {
		//echo "<pre>".print_r($_SESSION,1)."</pre>";
		//echo "<pre>".print_r($_GET,1)."</pre>";exit;
		$_SESSION['oauth_verifier'] = $_GET['oauth_verifier'];

		$dt_oauth = array(
			'oauth_verifier' => $_GET['oauth_verifier'],
			'oauth_token' => $_SESSION['oauth_token']
		);

		$oauth = token_verify($dt_oauth);

		if ($oauth['status'] == '200 OK') {
			$oauth_token_secret = $oauth['oauth_token_secret'];
			$oauth_token = $oauth['oauth_token'];
			$user_id = $oauth['user_id'];
			$screen_name = $oauth['screen_name'];

			$_SESSION['oauth_token_secret'] = $oauth_token_secret;
			$_SESSION['oauth_token'] = $oauth_token;
			$_SESSION['user_id'] = $user_id;	//twitter_id
			$_SESSION['screen_name'] = $screen_name;
			header('location:http://abdiid.com/scrape/ganon/index.php');
			exit;
		} else {	// respon from twitter not 200 ok
			die('error status not 200');
		}
	} else die(json_encode(array('error . Access denied')));
} else redirect(c_link('login/login').'?z='. ue('nav=gagal').'&y=empty');	// end step 2

