<?php
require_once('../../settingan/ketetapan.php');
require_once(SEKRIP_PATH.'code/tw_lib.php');

header('Content-type: application/json');

//~ cek session klo belum login return error
tw_conn();

//~ set query default dan custoum per request
//~ default
$limit = 20;
$page = 1;
$handle = 1;

//~ custom
if (!isset($_GET['page']) || !isset($_GET['code']) || !isset($_GET['search'])) {
  tw_err(400, "Queri pencarian bermasalah");
}
foreach($_GET as $k => $v ) {
  $$k = $v;
}
if (isset($_SESSION['limit'])) $limit = $_SESSION['limit'];
if (is_numeric($page)) $page = max($page, 1);
$file = tw_file($code);
$response = array(
  'iTotalPage' => 1,
  'iCurrentPage' => 1
);

//~ eksekusi query dengan menginclude file yang dibutuhkan
//~ per file already calling custom function
require_once($file);

if (!$output['success']){
  $status = isset($output['http_code'])?$output['http_code']:400;
  $msg = isset($output['error']['message'])?$output['error']['message']:"Unknown Error.";
  tw_err($status,$msg);
}
$default = array(
  'bFirstPage' => 0,
  'bLastPage' => 0,
  'iTotalPage' => 1000,
  'iCurrentPage' => 999
);
$response = array_merge($default, $response);
echo json_encode($response);
exit();
?>