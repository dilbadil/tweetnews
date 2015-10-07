<?php
require_once('../../settingan/ketetapan.php');
require_once(SEKRIP_PATH.'code/tw_lib.php');
header('Content-type: application/json');
tw_conn();
if (!isset($_SESSION['oauth_token']) || !isset($_SESSION['oauth_token'])){
  tw_err(407, 'Kamu belum login atau akun twitter kamu bermasalah. No token in session.');
}
if(isset($_SESSION['user_id'])) {
  $id = $_SESSION['user_id'];
} else {
  tw_err();
}
$sql = "SELECT twitter_id AS value, twitter_screenname AS text FROM ref_account WHERE user_id = $id";
if($resp = q($sql)) {
  die(json_encode($resp));
}
return false;