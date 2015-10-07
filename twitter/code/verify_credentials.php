<?php
require_once('../../settingan/ketetapan.php');
require_once(SEKRIP_PATH.'code/tw_lib.php');
header('Content-type: application/json');

//~ cek session klo belum login return error
tw_conn();

if (!isset($_SESSION['verified'])or isset($_GET['refresh'])) {
  $output = tw_verify();
  if ($output['success']) {
    //~ die(json_encode($output['response']));
    $dt = tw_render_user($output['response'], true);
  } else {
    return false;
  }
  $cursor = -1;
  $followers_ids = tw_followers_ids_all();
  //~ die(json_encode($user));
  $friends_ids = tw_friends_ids_all();
  $history = tw_history();

  $mutual = array_intersect($followers_ids,$friends_ids);
  $balas_budi = my_array_diff($followers_ids,$mutual);
  $flush = my_array_diff($friends_ids,$mutual);
  $followers_ids = $followers_ids;
  $friends_ids = $friends_ids;

  $dt['followers_ids_count']=count($followers_ids);
  $dt['friends_ids_count']=count($friends_ids);
  $dt['mutual_count']=count($mutual);
  $dt['balas_budi_count']=count($balas_budi);
  $dt['flush_count']=count($flush);

  if (isset($history['spark'])) $dt['spark'] = $history['spark'];
  if (isset($history['today'])) $dt['today'] = $history['today'];

  $_SESSION['pp']=$dt['user_img']['profile_image_url_https'];
  $_SESSION['verified']=1;
  $_SESSION['userdata']=array();
  $_SESSION['mutual'] = $mutual;
  $_SESSION['balas_budi'] = $balas_budi;
  $_SESSION['flush'] = $flush;
  $_SESSION['follower'] = $followers_ids;
  $_SESSION['friend'] = $friends_ids;

  die(json_encode($dt));
}
else {
  die(0);
}
?>