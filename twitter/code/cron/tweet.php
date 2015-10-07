<?php
require_once('../../../settingan/ketetapan.php');
require_once('../tw_lib.php');
//~ $cron = "crontab * * * * * cd /opt/lampp/htdocs/tweepi/sekrip/code/cron; php tweet.php";
//~ $cron = "/usr/bin/curl -o temp.txt http://projecto.com/tweepi/sekrip/code/cron/tweet.php
//~ ";
//~ $sql = "
//~ SELECT j.isi, a.token, a.token_secret
//~ FROM ref_jadwal as j
//~ JOIN ref_account as a
//~ ON j.twitter_id = a.twitter_id
//~ WHERE waktu <= now() + INTERVAL 5 MINUTE
//~ ";
$sql = "SELECT j.id, j.isi, j.gambar, a.token, a.token_secret FROM ref_jadwal as j JOIN ref_account as a ON j.twitter_id = a.twitter_id WHERE waktu <= now() AND aktif='yes'";
$arr = q($sql);
foreach($arr as $k => $v) {
  $token = array(
    'token' => $v['token'],
    'secret' => $v['token_secret']
  );
  $isi = $v['isi'];
  $id = $v['id'];
  $gambar = urldecode($v['gambar']);
  if ($gambar != '') {
    $resp = tw_photo_status_update_with_conn($isi, $gambar, 0, $token);
  } else {
    $resp = tw_status_update_with_conn($isi, 0, $token);
  }
  //~ if($resp['success'] == 1) {
  tw_deactive_jadwal($id);
  //~ }
  echo $resp;
}