<?php
require_once('../../../settingan/ketetapan.php');
header('Content-type: application/json');
require_once('_tmhoauth.php');
function tw_multi_conn($names) {
  //~ $connection = new tmhOAuth(array('token' => $_SESSION['oauth_token'], 'secret' => $_SESSION['oauth_token_secret']));
  $connection = new tmhOAuth();
  foreach($names as $name) {
    $connection->request('POST', $connection->url('1.1/friendships/create'), array('screen_name' => $name, 'follow' => true), true, false, array(), true);
  };
  return $connection->exec_multi();
}
//~ $connection = new tmhOAuth(array('token' => $_SESSION['oauth_token'], 'secret' => $_SESSION['oauth_token_secret']));
//~ $connection->request('POST', $connection->url('1.1/friendships/create'), array('screen_name' => 'vatih', 'follow' => true));
//~ echo '<pre>'.print_r($connection->response,1).'</pre>';exit();
//~ $names = array('vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','8EJ3','joewalnes','AlkesSolution','IzkandarMo','abduljpwd', 'asdadasdsadsadsad');
$names = array('vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','vatihsdad', 'vatih', 'abdiid','MRifqiAdiputra','bursatokoonline','vatihsdad');
$resp = tw_multi_conn($names);
echo '<pre>'.print_r($resp,1).'</pre>';exit();



