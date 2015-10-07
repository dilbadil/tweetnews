<?php
require_once('../../settingan/ketetapan.php');
header('Content-type: application/json');
//~ cek session klo belum login return sesuatu
//~ limit di set 20 dulu
$limit = 20;
if (isset($_SESSION['limit'])) {
  $limit = $_SESSION['limit'];
}
$search = $_GET['search'];
$code = $_GET['code'];
$page = $_GET['page'];
$start = ($page - 1) * $limit;
switch ($code) {
  case 'follower';
    $file = '/code/inc3/follower.php';
    break;
  case 'following';
    $file = '/code/inc3/following.php';
    break;
  case 'balas_budi';
    $file = '/code/inc3/balas_budi.php';
    break;
  case 'tweet';
    $file = '/code/inc3/tweet.php';
    break;
  case 'bio';
    $file = '/code/inc3/bio.php';
    break;
  case 'list';
    $file = '/code/inc3/list.php';
    break;
  case 'list_member';
    $file = '/code/inc3/list_member.php';
    break;
  case 'flush';
    $file = '/code/inc3/flush.php';
    break;
  default :
    break;
}
require(LIB_PATH.'oauth_twitter/config.php');
require(LIB_PATH.'oauth_twitter/oauth_lib.php');
$connection = get_connection(array('user_token'=>$_SESSION['oauth_token'],'user_secret'=>$_SESSION['oauth_token_secret']));
require_once(SEKRIP_PATH.$file);
$output['error'] = json_decode($connection->response['error'], true);
$output['json_table'] = array();
echo json_encode($output);
exit();