<?php
function get_connection($config=array()) {
	require('tmhOAuth.php');
	$connection = new tmhOAuth(array_merge(
		array(
			  'consumer_key'    => 'LN7VLxtQKjFXsHPOIIM4zQ',
			  'consumer_secret' => 'gRmPQXnu0smxhFS3fYYUe1aSp18INC7Fu76ld1JM'
		),
		$config
		  //~ ,
			  //~ 'user_token'      => $user_token,
			  //~ 'user_secret'     => $user_secret
	));

	return $connection;
}
?>
