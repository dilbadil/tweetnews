<?php
require_once('../../settingan/ketetapan.php');
require_once(SEKRIP_PATH.'code/tw_lib.php');
tw_conn();
//~ if ( (!isset($_POST['follow']) && !isset($_POST['unfollow']) && !isset($_POST['forceunfollow'])) || (empty($_POST['follow']) && empty($_POST['unfollow']) && empty($_POST['forceunfollow']) )){
if ( !isset($_POST['q']) || (empty($_POST['q']) )){
  tw_err(400, "Queri yang diminta bermasalah");
}
if (!isset($_SESSION['oauth_token']) || !isset($_SESSION['oauth_token'])){
  tw_err(407, 'Kamu belum login atau akun twitter kamu bermasalah. No token in session.');
}
set_time_limit(1200);
$follow_session = false;
if (isset($_SESSION['follow_session']))$follow_session = $_SESSION['follow_session'];
$follow_session = false;
session_write_close();
$q = json_decode($_POST['q']);
foreach ($q as $k => $v) {
  $$k = $v;
}
$success = array();
$fail = array();

//follow
if (isset($follow)) {
  //~ $url = '1.1/friendships/create';
  //~ $f_resp = tw_multi_act($follow, $url);
  //~ $fo = tw_response_act($follow, $f_resp);
  //~ $success['follow'] = $fo['success'];
  //~ $fail['follow'] = $fo['fail'];

  //~ jika follow_session belum jalan
  ini_set('session.use_only_cookies', false);
  ini_set('session.use_cookies', false);
  ini_set('session.use_trans_sid', false);
  ini_set('session.cache_limiter', null);
  if (!$follow_session) {
    session_start(); // second session_start
    $_SESSION['follow_session'] = true;
    session_write_close();
    $success['follow'] = array();
    $fail['follow'] = array();
    $conn = tw_get_connection();
    $url = '1.1/friendships/create';
    $curl = $conn->url($url);
    $resps = array();
    while(!empty($follow)) {
      foreach($follow as $name) {
        $arr = array(
          'screen_name' => $name
        );
        $http_code = $conn->request('POST', $curl, $arr);
        $resps[] = json_decode($conn->response['response']);
      }
      //~ echo '<pre>'.print_r($resps,1).'</pre>';exit();
      $fo = tw_response_act($follow, $resps);
      $success['follow'] = array_merge($success['follow'], $fo['success']);
      $fail['follow'] = array_merge($fail['follow'], $fo['fail']);
      $follow = array();
      $resps = array();
      session_start(); // second session_start
      if(isset($_SESSION['follow_queue']))$follow = $_SESSION['follow_queue'];
      $_SESSION['follow_queue'] = [];
      session_write_close();
    }
    session_start(); // second session_start
    $_SESSION['follow_session'] = false;
    session_write_close();

  } else {
    session_start(); // second session_start
    if (isset($_SESSION['follow_queue'])) {
      $_SESSION['follow_queue'] = array_merge($_SESSION['follow_queue'], $follow);
    } else {
      $_SESSION['follow_queue'] = $follow;
    }
    session_write_close();
  }
}

//unfollow
if (isset($unfollow)) {
  $url = '1.1/friendships/destroy';
  $u_resp = tw_multi_act($unfollow, $url);
  //~ die(json_encode($u_resp));
  $unfollow = tw_response_act($unfollow, $u_resp);

  $success['unfollow'] = $unfollow['success'];
  $fail['unfollow'] = $unfollow['fail'];
}

//force unfollow
if (isset($forceunfollow)) {
  $url = '1.1/blocks/create';
  $fu1_resp = tw_multi_act($forceunfollow, $url);
  $fu1 = tw_response_act($forceunfollow, $fu1_resp);
  sleep(60);
  $url = '1.1/blocks/destroy';
  $fu_resp = tw_multi_act($forceunfollow, $url);
  $forceunfollow = tw_response_act($forceunfollow, $fu_resp);

  $success['forceunfollow'] = $forceunfollow['success'];
  $fail['forceunfollow'] = $forceunfollow['fail'];
}

//retweet
if (isset($retweet)) {
  $url = '1.1/statuses/retweet';
  $rt_resp = tw_multi_rt($retweet, $url);
  //~ die(json_encode($rt_resp));
  $rto = tw_response_act($retweet, $rt_resp);
  $success['retweet'] = $rto['success'];
  $fail['retweet'] = $rto['fail'];
}

//reply
if (isset($reply)) {
  $tweets = array();
  $ids = array();
  foreach($reply as $k => $v) {
    $tweets[] = $v->text;
    $ids[] = $v->id;
  }
  $re_resp = tw_status_update($tweets, $ids);
  $reo = tw_response_act($ids, $re_resp);
  $success['tweet'] = $reo['success'];
  $fail['tweet'] = $reo['fail'];
}

//tweet
if (isset($tweet)) {
  $tw_resp = tw_status_update($tweet);
  $two = tw_response_act($tweet, $tw_resp);

  $fail = array();
  foreach($two['fail']['name'] as $k => $v) {
    $two['fail']['name'][$k] = '';
  }
  $success['tweet'] = $two['success'];
  $fail['tweet'] = $two['fail'];
}

//mention
if (isset($mention)) {
  $me_resp = tw_status_update($mention);
  $meo = tw_response_act($mention, $me_resp);
  $success['tweet'] = $meo['success'];
  $fail['tweet'] = $meo['fail'];
}

//favorite
if (isset($favorite)) {
  $fv_resp = tw_multi_fav($favorite);
  //~ die(json_encode($fv_resp));
  $fvo = tw_response_act($favorite, $fv_resp);

  $success['favorite'] = $fvo['success'];
  $fail['favorite'] = $fvo['fail'];
}

//penjadwalan tweet
if (isset($penjadwalan_tweet)) {
  if ($ids = tw_add_jadwal($penjadwalan_tweet[0])) {
    $success['penjadwalan_tweet'] = tw_get_jadwal_by_id_arr($ids);;
  } else {
    $fail['penjadwalan_tweet'] = false;
  }
}

//penjadwalan berulang
if (isset($penjadwalan_berulang)) {
  if ($ids = tw_add_berulang($penjadwalan_berulang[0])) {
    $success['penjadwalan_berulang'] = tw_get_jadwal_by_id_arr($ids);;
  } else {
    $fail['penjadwalan_berulang'] = false;
  }
}

//edit penjadwalan tweet
if (isset($edit_penjadwalan_tweet)) {
  if ($id = tw_edit_jadwal($edit_penjadwalan_tweet[0])) {
    $success['edit_penjadwalan_tweet'] = tw_get_jadwal_by_id($id);
  } else {
    $fail['edit_penjadwalan_tweet'] = false;
  }
  //~ echo '<pre>'.print_r($edit_penjadwalan_tweet,1).'</pre>';exit();
}

//hapus_tweet
if (isset($hapus_tweet)) {
  if(!empty($hapus_tweet)){
    $ids = implode(',', $hapus_tweet);
    if ($result = tw_remove_jadwal($ids)){
      $success['hapus_tweet'] = $hapus_tweet;
    } else {
      $fail['hapus_tweet'] = $hapus_tweet;
    }
  }
}

//kultweet
if (isset($kultweet)){
  if ($ids = tw_add_kultweet($kultweet[0])) {
    $success['kultweet'] = tw_get_kultweet_by_id_arr($ids);;
  } else {
    $fail['kultweet'] = false;
  }
}

//edit penjadwalan tweet
if (isset($edit_kultweet)) {
  if ($id = tw_edit_kultweet($edit_kultweet[0])) {
    $success['edit_kultweet'] = tw_get_kultweet_by_id($id);
  } else {
    $fail['edit_kultweet'] = false;
  }
  //~ echo '<pre>'.print_r($edit_penjadwalan_tweet,1).'</pre>';exit();
}

//hapus_kultweet
if (isset($hapus_kultweet)) {
  if(!empty($hapus_kultweet)){
    $ids = implode(',', $hapus_kultweet);
    if ($result = tw_remove_kultweet($ids)){
      $success['hapus_kultweet'] = $hapus_kultweet;
    } else {
      $fail['hapus_kultweet'] = $hapus_kultweet;
    }
  }
}

//tweet kultweet
if (isset($tweet_kultweet)){
  $user_id = $_SESSION['user_id'];
  $success['tweet_kultweet'] = array();
  $fail['tweet_kultweet'] = array('code' => array(), 'name'=> array());
  $tweet_kultweet = (array) $tweet_kultweet[0];
  $last = false;
  if(isset($tweet_kultweet['last'])) {
    $last = $tweet_kultweet['last'];
    $id = $tweet_kultweet['id'];
  }
  $akun = $tweet_kultweet['akun'];
  $isi = $tweet_kultweet['isi'];
  foreach($akun as $k => $v) {
    if(!isset($_SESSION['live_tweet']) ) {
      $_SESSION['live_tweet'] = array();
    }
    if(isset($_SESSION['live_tweet'][$v])) {
      $token = $_SESSION['live_tweet'][$v];
    } else {
      $query = "SELECT token, token_secret FROM ref_account WHERE twitter_id = $v AND user_id = $user_id";
      $res = q($query);
      $token = array(
        'user_token' => $res[0]['token'],
        'user_secret' => $res[0]['token_secret']
      );
      $_SESSION['live_tweet'][$v] = $token;
    }
    $output = tw_status_update_with_conn($isi, 0, $token);
    if ($output['success']) {
      $success['tweet_kultweet'][] = $v;
    } else {
      $resp = $output['response'];
      if (is_null($resp)) {
        $fail['tweet_kultweet']['name'][] = $v;
        $fail['tweet_kultweet']['code'][] = 0;
      } else if (isset($resp->errors)) {
        if (is_object($resp->errors[0])) {
          $fail['tweet_kultweet']['code'][] = $resp->errors[0]->code;
        } else {
          $fail['tweet_kultweet']['code'][] = $resp->errors;
        }
        $fail['tweet_kultweet']['name'][] = $v;
      } else if (isset($resp->response['headers']['status']) &&  $resp->response['headers']['status'] == '403 Forbidden') {
        $fail['tweet_kultweet']['name'][] = $v;
        $fail['tweet_kultweet']['code'][] = 403;
      } else if (isset($resp->response['headers']['status']) &&  $resp->response['headers']['status'] == '404 Not Found'){
        $fail['tweet_kultweet']['name'][] = $v;
        $fail['tweet_kultweet']['code'][] = 404;
      } else if (isset($resp->response['headers']['status']) &&  $resp->response['headers']['status'] == '200 OK'){
        $out['success'][] = $in[$i];
      } else if (isset($resp->id_str)){
        $out['success'][] = $resp->id_str;
      } else {
        echo '<pre>'.print_r($resp,1).'</pre>';exit();
      }
      //~ echo '<pre>'.print_r($fail,1).'</pre>';exit();
      //~ $fail['tweet_kultweet']['name'][] = $v;
      //~ $fail['tweet_kultweet']['code'][] = $output['response']->errors[0]->code;
    }
  }
  if ($last) {
    $res = tw_deactive_kultweet($id);
  }
}

$output = array(
  'success' => $success,
  'fail' => $fail
);

$debug = 0;
if ($debug) {
  $output['debug'] = array(
    'resp' => $rt_resp,
    'in' => $retweet,
    'out' => $rto
  );
}

die(json_encode($output));
?>
