<?php
require_once(LIB_PATH.'oauth_twitter/config.php');
require_once(LIB_PATH.'oauth_twitter/oauth_lib.php');
define('MAX_RET_DATA', 50000);
define('MAX_CONN_TRY', 3);

/**
 * cek fungsi http_response_code untuk php > 5.5
 * jika not exist insert polyfill
 */
if (!function_exists('http_response_code')) {
  function http_response_code($newcode = NULL) {
    static $code = 200;
    if($newcode !== NULL) {
      header('X-PHP-Response-Code: '.$newcode, true, $newcode);
      if(!headers_sent()) $code = $newcode;
    }
    return $code;
  }
}

/**
 * fungsi yang mengatur header HTTP jika terjadi error saat ajax
 * @param int $status HTTP status
 * @param string $msg Pesan kesalahan
 */
function tw_err($status = 404, $msg = "Unknown Request", $notif = false) {
  http_response_code($status);
  $output = array(
    'error' => true,
    'error_message' => $msg,
    'error_code' => $status
  );
  if ($notif) {
    return $output;
  }
  die(json_encode($output));
}

/**
 * fungsi yang mengatur header HTTP jika terjadi error saat ajax tanpa die()
 */
function tw_err_notif($status = 404, $msg = "Unknown Request") {
  tw_err($status, $msg, true);
}

/**
 * Fungsi mengecek dan mempersiapkan koneksi ke twitter, mengambil data dari session
 */
function tw_conn() {
  //~ cek sudah login belum
  if (!isset($_SESSION['oauth_token']) || !isset($_SESSION['oauth_token_secret'])) {
    //~ belum, maka cek cookie
    if(!isset($_COOKIE['sess_id'])) {
      //~ no cookie
      tw_err(403, 'Kamu belum login atau akun twitter kamu bermasalah. No token in session');
    } else {
      //~ cookie
      require_once(BASE_PATH.'/modul/login/login_m.php');
      cek_cookie(true);
    }
  }
  if (isset($_SESSION['sisa_hari'])) {
    $sisa_hari = $_SESSION['sisa_hari'];
    if($sisa_hari > -1) {
      $limit = 200;
    } else {
      $limit = 20;
      if(isset($_SESSION['other_groups']) && isset($_SESSION['other_groups']['semi_premium']) && $_SESSION['other_groups']['semi_premium'] == true) {
        //~ untuk bonus 45
        if($_SESSION['other_groups']['semi_premium_id'] == 4) $limit = 45;
      }
    }
    $_SESSION['limit'] = $limit;
  } else {
    //~ belum, maka suruh login
    $msg = 'Kamu belum login atau akun '.APP_NAME.' kamu belum teraktivasi';
    tw_err(401, $msg);
  }
  return 1;
}

/**
 * Fungsi mengecek file yang dibutuhkan
 * @param string $code
 */
function tw_file($code) {
  $file = SEKRIP_PATH.'code/inc/'.$code.'.php';
  if (!file_exists($file)) tw_err(400, "Queri pencarian bermasalah");
  return $file;
}

/**
 * Fungsi membuat koneksi baru, jika ingin menggunakan token selain di session
 * @param mixed $conn array berisi oauth_token dan oauth_token_secret
 */
function tw_get_connection($conn = false) {
  if ($conn === false) {
    $connection = get_connection(array('user_token'=>$_SESSION['oauth_token'],'user_secret'=>$_SESSION['oauth_token_secret']));
  } else {
    $connection = get_connection($conn);
  }
  return $connection;
}

/**
 * Fungsi wrapper connection request berguna untuk melakukan pengulangan MAX_CONN_TRY kali jika data gagal diambil
 * @param string $url url twitter rest api
 * @param mixed $arr array yang berisi parameter rest api
 * @param string $method 'GET'|'POST'
 * @param bool $notif untuk die json atau return
 */
function tw_request($url, $arr = array(), $method = 'GET', $notif = false) {
  $count = 0;
  $http_code = '';
  $error = '';
  $conn = tw_get_connection();
  while ($http_code != 200) {
    $conn->request($method, $conn->url($url),$arr);
    $http_code = $conn->response['code'];
    $error = isset($conn->response['errors'])? $conn->response['errors']:'';
    $errors = isset($conn->response['error'])? $conn->response['error']:'';
    if (++$count == MAX_CONN_TRY) {
      break;
    }
  }
  $success = ($http_code == 200 && empty($error) && empty($errors));
  if ($success) {
    $response = json_decode($conn->response['response'], true);
    $output = array(
      'response' => $response,
      'http_code' => $http_code,
      'error' => $error,
      'success' => $success
    );
  } else {
    $response = json_decode($conn->response['response']);
    if (isset($response->errors)) {
      $message = 'Twitter error code: '. $response->errors[0]->code.'. '.$response->errors[0]->message;
      $message = trim($message, '.').'. '.tw_code($response->errors[0]->code);
      $code = $http_code;
    } else if (isset($response->error)) {
      $code = $http_code;
      $message = $response->error;
    }
    if ($notif) {
      $output = tw_err_notif($code, $message);
    } else {
      tw_err($code, $message);
    }
  }
  return $output;
}


/**
 * Fungsi wrapper connection request berguna untuk melakukan pengulangan MAX_CONN_TRY kali jika data gagal diambil
 * @param string $url url twitter rest api
 * @param mixed $arr array yang berisi parameter rest api
 * @param string $method 'GET'|'POST'
 * @param bool $notif untuk die json atau return
 */
function tw_request_with_conn($url, $arr = array(), $method = 'GET', $conn, $userauth=true, $multipart=false) {
  $count = 0;
  $http_code = '';
  $error = '';
  $conn = tw_get_connection($conn);
  while ($http_code != 200) {
    $conn->request($method, $conn->url($url),$arr, $userauth, $multipart);
    $http_code = $conn->response['code'];
    $error = isset($conn->response['errors'])? $conn->response['errors']:'';
    $errors = isset($conn->response['error'])? $conn->response['error']:'';
    if (++$count == MAX_CONN_TRY) {
      break;
    }
  }
  $success = ($http_code == 200 && empty($error) && empty($errors));
  if ($success) {
    $response = json_decode($conn->response['response'], true);
    $output = array(
      'response' => $response,
      'http_code' => $http_code,
      'error' => $error,
      'success' => $success
    );
  } else {
    $response = json_decode($conn->response['response']);
    if (isset($response->errors)) {
      $message = 'Twitter error code: '. $response->errors[0]->code.'. '.$response->errors[0]->message;
      $message = trim($message, '.').'. '.tw_code($response->errors[0]->code);
      $code = $http_code;
    } else if (isset($response->error)) {
      $code = $http_code;
      $message = $response->error;
    }
    //~ $output = tw_err_notif($code, $message);
    $output = array(
      'response' => $response,
      'http_code' => $http_code,
      'error' => $error,
      'success' => $success
    );
  }
  return $output;
}

/**
 * Fungsi wrapper connection request dengan multi curl
 * @param mixed $arrays array of array parameter request yang dikirim ke twitter
 * @param string $url url api twitter
 * @param string $method 'GET'|'POST'
 */
function tw_multi_request($arrays, $url, $method = 'GET') {
	$connection = tw_get_connection();
  foreach($arrays as $array) {
    $connection->request($method, $connection->url($url), $array, true, false, array(), true);
  };
  return $connection->exec_multi();
}

/**
 * Fungsi wrapper connection request dengan multi curl
 * @param mixed $arrays array of array parameter request yang dikirim ke twitter
 * @param string $url url api twitter
 * @param string $method 'GET'|'POST'
 */
function tw_multi_url_request($arrays, $urls, $method = 'GET') {
	$connection = tw_get_connection();
  $i = 0;
  foreach($arrays as $array) {
    $connection->request($method, $connection->url($urls[$i++]), $array, true, false, array(), true);
  };
  return $connection->exec_multi();
}

/**
 * Fungsi ini sama dengan di atas tapi tanpa fungsi tw_err_notif()
 */
function tw_request_notif($url, $arr, $method = 'GET') {
  return tw_request($url, $arr, $method, true);
}

/**
 * Fungsi merubah code error twitter ke code error http
 * @param int $code kode twitter
 */
function tw_code($code) {
  switch ($code) {
    case 34;
      $resp = 'Hasil pencarian kosong.';
      break;
    case 32;
    case 89;
    case 215;
      $resp = 'Silahkan mengenerate token baru di halaman twitter. Klik <a href="'.c_link('login/login')."?z=".ue('nav=via_twitter').'&y=exit" onclick="window.open(this.href, \'popupWindow\', \'width=600, height=400, scrollbars=yes\');return false;">di sini</a>.';
      break;
    case 88;
      $resp = 'Jatah penggunaan API twitter kamu habis. Tunggu sekitar 15 menit. Lain kali jangan asal klik ya.';
      break;
    case 64;
      $resp = 'Akun kamu di suspend! Coba buka twitter dan minta diaktifkan kembali. Klik di <a href="http://twitter.com/" onclick="window.open(this.href, \'popupWindow\', \'width=600, height=400, scrollbars=yes\');return false;">di sini</a>.';
      break;
    case 130;
      $resp = 'Twitter sedang error. Harap sabar.';
      break;
    case 161;
      $resp = 'Jatah follow untuk hari ini sudah habis. Silahkan coba lagi besok.';
      break;
    case 179;
      $resp = 'Akun ini protected. Hanya bisa di lihat oleh orang yang diizinkan';
      break;
    case 185;
      $resp = 'Jatah tweet kamu hari ini sudah habis. Selamat ya. Coba lagi besok.';
      break;
    case 187;
      $resp = 'Tidak boleh tweet dengan isi yang sama bersamaan. Jika tidak merasa melakukan tweet 2 kali, abaikan saja.';
      break;
    case 161;
      $resp = 'Jatah follow untuk hari ini sudah habis. Silahkan coba lagi besok.';
      break;
    default :
      $resp = '';
  }
  return $resp;
}

/**
 * Fungsi mengecek user yang login melalui twitter api
 *
 */
function tw_verify() {
  $url = '1.1/account/verify_credentials';
  return tw_request($url);
}

/**
 * Fungsi mengambil data 1 user
 * @param string $search twitter screen name
 * @param bool $entity jika defined/true, data termasuk entity tambahan seperti last tweet dan retweet
 * @param bool|array $conn jika kosong, akan menggunakan user_token dan user_token_secret default, untuk parameter selain default masukkan array dengan property sesuai param yang diinginkan
 */
function tw_user($search, $entity = true, $conn = '', $notif = false) {
  $search = preg_replace('/\s+/', '', $search);
  $url = '1.1/users/show';
  $arr = array(
    'include_entities' => $entity,
    'screen_name' => $search
  );
  if ($notif) {
    return tw_request_notif($url, $arr);
  }
  return tw_request($url, $arr);
}

/**
 * Fungsi mengambil data 1 user agus => untuk notifikasi;
 */
function tw_user_notif($search, $entity = false, $conn = '') {
  return tw_user($search, $entity, $conn, true);
}

/**
 * Fungsi mengambil data follower id
 * @param string $search twitter screen name
 * @param int $count jumlah id yang ingin diambil, default 5000
 * @param bool|array $conn jika kosong, akan menggunakan user_token dan user_token_secret default, untuk parameter selain default masukkan array dengan property sesuai param yang diinginkan
 */
function tw_followers_ids($search = '', $count = 5000, $handle = 1, $cursor = -1) {
  $ids_count = isset($_SESSION[$search.'_follower_ids']) ? count($_SESSION[$search.'_follower_ids']) : 0;
  $url = '1.1/followers/ids';
  if ($handle == 1) {
    $cursor = -1;
  } else {
    if ($cursor == -1) {
      $cursor = isset($_SESSION[$search.'_follower_cursor'])? $_SESSION[$search.'_follower_cursor']:-1;
    }
    if ($cursor == 0 || $ids_count > MAX_RET_DATA) {
      return array(
        'success' => false
      );
    }
  }
  $arr = array(
    'cursor' => $cursor,
    'count' => $count,
    'screen_name' => $search,
    'stringify_ids' => true
  );
  return tw_request($url, $arr);
}

/**
 * Fungsi mengambil semua follower ids (max sejumlah MAX_RET_DATA) milik akun verify/login
 */
function tw_followers_ids_all(){
  $output = tw_followers_ids();
  $resp = array();
  if ($output['success']) {
    $resp = $output['response']['ids'];
  }
  return $resp;
}
/**
 * Fungsi mengambil data friend id
 * @param string $search twitter screen name
 * @param int $count jumlah id yang ingin diambil, default 5000
 * @param bool|array $conn jika kosong, akan menggunakan user_token dan user_token_secret default, untuk parameter selain default masukkan array dengan property sesuai param yang diinginkan
 */
function tw_friends_ids($search = '', $count = 5000, $handle = 1, $conn = false) {
  $ids_count = isset($_SESSION[$search.'_friend_ids']) ? count($_SESSION[$search.'_friend_ids']) : 0;
  $url = '1.1/friends/ids';
  if ($handle == 1) {
    $cursor = -1;
  } else {
    $cursor = isset($_SESSION[$search.'_friend_cursor'])? $_SESSION[$search.'_friend_cursor']:-1;
    if ($cursor == 0 || $ids_count > MAX_RET_DATA) {
      return array(
        'success' => false
      );
    }
  }
  $arr = array(
    'cursor' => $cursor,
    'count' => $count,
    'screen_name' => $search,
    'stringify_ids' => true
  );
  return tw_request($url, $arr);
}

/**
 * Fungsi mengambil semua friend ids (max sejumlah MAX_RET_DATA) milik akun verify/login
 */
function tw_friends_ids_all(){
  $output = tw_friends_ids();
  $resp = array();
  if ($output['success']) {
    $resp = $output['response']['ids'];
  }
  return $resp;
}

/**
 * Fungsi mengambil data banyak user
 * @param string $search twitter screen name
 * @param string $ids comma separated twitter user ids
 * @param string $code jika empty, follower. Masukan untuk url rest api.
 * @param bool $entity jika defined/true, data termasuk entity tambahan seperti last tweet dan retweet
 * @param bool|array $conn jika kosong, akan menggunakan user_token dan user_token_secret default, untuk parameter selain default masukkan array dengan property sesuai param yang diinginkan
 */
function tw_multi_user($search, $start, $code = 'follower_ids', $entity = false, $conn = false) {
  $url = '1.1/users/lookup';
  $count = $_SESSION['limit'];
  if ($code == 'ceplok_telur' || $code == 'tanpa_bio') {
    $count = $_SESSION['limit'] * 10;
  }
  $output = array(
    'http_code' => 404,
    'error' => array('message' => "Tidak ada user/data."),
    'success' => false
  );
  --$start;
  $ids = array($start);
  while($count > 100) {
    $start += 100;
    $ids[] = $start;
    $count -= 100;
  }
  $length = min($_SESSION['limit'], 100);
  $data_user = array();
  switch ($code) {
    case 'follower_ids';
    case 'friend_ids';
      $session = $search.'_'.$code;
      break;
    case 'balas_budi';
      $session = 'balas_budi';
      break;
    case 'flush';
      $session = 'flush';
      break;
    case 'ceplok_telur';
    case 'unfollow_all';
    case 'tanpa_bio';
      $session = 'friend';
      break;
    default :
      $session = 'default';
      break;
  }
  foreach($ids as $id) {
    $user_id = array_slice($_SESSION[$session], $id, $count);
    if (empty($user_id)) break;
    $arr = array(
      'include_entities' => $entity,
      'user_id' => $user_id
    );
    $output = tw_request($url, $arr);
    if ($output['success']) {
      foreach($output['response'] as $k => $v) {
        $data_user[] = tw_render_user($v);
      }
    } else break;
  }
  $http_code = $output['http_code'];
  $error = $output['error'];
  $success = $output['success'];
  $output2 = array(
    'response' => $data_user,
    'http_code' => $http_code,
    'error' => $error,
    'success' => $success
  );
  return $output2;
}

/**
 * Fungsi mengambil data list yang dimiliki $search
 * @param string $search twitter screen name
 * @param int $cursor cursor untuk pagin
 * @param int $count jumlah list yang ingin ditamplikan
 */
function tw_list($search, $start, $cursor = -1, $count = 1000) {
  $url = '1.1/lists/ownerships';
  $data_list = array();
  $search = str_replace(' ', '', $search);
  $arr = array(
    'screen_name' => $search,
    'cursor' => $cursor,
    'count' => $count
  );
  $cursor = 0;
  $output = tw_request($url, $arr);
  if ($output['success'] && count($output['response']['lists']) > 0) {
    foreach ($output['response']['lists'] as $k => $v) {
      $data_list[] = tw_render_list($v);
    }
  }
  $cursor = $output['response']['next_cursor_str'];
  $http_code = $output['http_code'];
  $error = $output['error'];
  $success = $output['success'];
  $output2 = array(
    'response' => $data_list,
    'http_code' => $http_code,
    'error' => $error,
    'success' => $success,
    'cursor' => $cursor
  );
  return $output2;
}

/**
 * Fungsi mengambil data member list yang dimiliki $search/$slug
 * @param string $search twitter screen name
 * @param int $cursor cursor untuk pagin
 * @param int $count jumlah list yang ingin ditamplikan
 */
function tw_list_member($search, $page = -1) {
  $url = '1.1/lists/members';
  $cursor = -1;
  if ($page == 'next') $cursor = $_SESSION[$search.'list_member_next_cursor'];
  if ($page == 'previous') $cursor = $_SESSION[$search.'list_member_previous_cursor'];
  $search = explode('/', $search);
  $slug = str_replace(' ', '-', $search[1]);
  $owner_screen_name = $search[0];
  $arr = array(
    'slug' => $slug,
    'owner_screen_name' => $owner_screen_name,
    'cursor' => $cursor
  );
  $data_user = array();
  $cursor = 0;
  $output = tw_request($url, $arr);
  if ($output['success'] && count($output['response']['users']) > 0) {
    foreach($output['response']['users'] as $k => $v) {
      $data_user[] = tw_render_user($v);
    }
  }
  $next = $output['response']['next_cursor_str'];
  $previous = $output['response']['previous_cursor_str'];
  $http_code = $output['http_code'];
  $error = $output['error'];
  $success = $output['success'];
  $output2 = array(
    'response' => $data_user,
    'http_code' => $http_code,
    'error' => $error,
    'success' => $success,
    'next' => $next,
    'previous' => $previous
  );
  return $output2;
}

/**
 * Fungsi mencari tweet
 * @param string $search tweet keyword url-encoded
 * @param string $page halaman (1|next|previous)
 * @param int $count jumlah result (0 - 100)
 * @param string $type jenis tweet (recent|mixed|popular)
 */
function tw_tweet_search($search, $page = 1, $count = 100, $type = 'recent') {
  $count = min($_SESSION['limit'], $count, 100);
  $url = '1.1/search/tweets';
  $arr = array(
    'q' => $search,
    'result_type' => $type,
    'count' => $count,
    'include_entities' => false
  );
  if ($page != 1) {
    if ($page == 'next' && isset($_SESSION[$search.'tweet_search_next_cursor'])) $arr['max_id'] = $_SESSION[$search.'tweet_search_next_cursor'];
    if ($page == 'previous' && isset($_SESSION[$search.'tweet_search_previous_cursor'])) $arr['since_id'] = $_SESSION[$search.'tweet_search_previous_cursor'];
  }
  $output = tw_request($url, $arr);
  $data_statuses = array();
  if ($output['success'] && count($output['response']['statuses']) > 0) {
    $i = 0;
    foreach($output['response']['statuses'] as $status) {
      $data_statuses[] = tw_render_tweet($status);
      if (isset($status['id_str'])) {
        if ($i++ == 0) $first_status_id = $status['id_str'];
        $last_status_id = $status['id_str'];
      }
    }
  }
  $next = isset($last_status_id)? $last_status_id: 0;
  $previous = isset($first_status_id)? $first_status_id: 0;
  $http_code = $output['http_code'];
  $error = $output['error'];
  $success = $output['success'];
  $output2 = array(
    'response' => $data_statuses,
    'http_code' => $http_code,
    'error' => $error,
    'success' => $success,
    'page' => $page,
    'next' => $next,
    'previous' => $previous
  );
  return $output2;
}

/**
 * Fungsi mencari user bio
 * @param string $search tweet keyword url-encoded
 * @param string $page halaman (1|next|previous)
 */
function tw_bio_search($search, $page = 1) {
  $url = '1.1/users/search';
  $type = $page;
  $count = 20;
  if ($page != 1 && isset($_SESSION[$search.'_bio_search_page'])) {
    if ($page == 'next') {
      $page = $_SESSION[$search.'_bio_search_page'] + 1;
    } else {
      $page = $_SESSION[$search.'_bio_search_page'] - 1;
    }
  } else $page = 1;
  $arr = array(
    'q' => $search,
    'count' => $count,
    'include_entities' => false,
    'page' => $page
  );
  $output = tw_request($url, $arr);
  $data_user = array();
  $last_user_id = 0;
  if ($output['success'] && count($output['response']) > 0) {
    $previous_last_user_id = isset($_SESSION[$search.'_bio_search_page_cursor'])? $_SESSION[$search.'_bio_search_page_cursor']: 0;
    $i = 1;
    foreach($output['response'] as $user) {
      $data_user[] = tw_render_user($user);
      $last_user_id = $user['id_str'];
      if ($previous_last_user_id == $last_user_id) {
        $index_hapus = $i;
      }
      $i++;
    }
  }
  if (isset($index_hapus) && $type == 'next') {
    array_splice($data_user, 0, $index_hapus);
  }
  $http_code = $output['http_code'];
  $error = $output['error'];
  $success = $output['success'];
  $output2 = array(
    'response' => $data_user,
    'http_code' => $http_code,
    'error' => $error,
    'success' => $success,
    'page' => $page,
    'last_user_id' => $last_user_id
  );
  return $output2;
}

/**
 * Fungsi mengupdate status secara umum
 * @param string $text tweet text
 * @param int $id in_reply_to_status_id
 */
function tw_status_update($tweets, $ids = array()) {
  $url = '1.1/statuses/update';
  $arrays = array();
  foreach($tweets as $i => $tweet) {
    $array = array(
      'status' => $tweet
    );
    if (isset($ids[$i])) $array['in_reply_to_status_id'] = $ids[$i];
    $arrays[] = $array;
  };
  return tw_multi_request($arrays, $url, 'POST');
}

/**
 * Fungsi mengupdate status secara token
 * @param string $text tweet text
 * @param int $id in_reply_to_status_id
 * @param $conn array of token
 */
function tw_status_update_with_conn($text, $id = 0, $conn) {
  $url = '1.1/statuses/update';
  $arr = array(
    'status' => $text
  );
  if ($id != 0) {
    $arr['in_reply_to_status_id'] = $id;
  }
  return tw_request_with_conn($url, $arr, 'POST', $conn);
}

/**
 * Fungsi mengupdate status secara token dengan media
 * @param string $text tweet text
 * @param string $gambar tweet text
 * @param int $id in_reply_to_status_id
 * @param $conn array of token
 */
function tw_photo_status_update_with_conn($isi, $gambar, $id = 0, $conn) {
  $image = UPLOAD_PATH.$gambar;
  $name  = basename($image);
  $status = $isi;
  $url = '1.1/statuses/update_with_media';
  $type = 'jpeg';
  $arr = array(
    'media[]'  => "@{$image};type=image/{$type};filename={$name}",
    'status' => $status
  );
  if ($id != 0) {
    $arr['in_reply_to_status_id'] = $id;
  }
  return tw_request_with_conn($url, $arr, 'POST', $conn, true, true);
}

/**
 * Fungsi ini melakukan multi exec request khusus untuk follow/unfollow/forceunfollow
 * @param mixed $names nama-nama/ids yang ingin di follow/unfollow/forceunfollow
 * @param string $url url api twitter
 */
function tw_multi_act($names, $url) {
  $arrays = array();
  foreach($names as $name) {
    $arrays[] = array(
      'screen_name' => $name
    );
  };
  return tw_multi_request($arrays, $url, 'POST');
}

/**
 * Fungsi ini melakukan multi exec request khusus untuk favorite
 * @param mixed $ids ids tweet yang ingin di favorite
 * @param string $url url api twitter
 */
function tw_multi_fav($ids, $url = '1.1/favorites/create') {
  $arrays = array();
  foreach($ids as $id) {
    $arrays[] = array(
      'id' => $id
    );
  };
  return tw_multi_request($arrays, $url, 'POST');
}

/**
 * Fungsi ini melakukan multi exec request khusus untuk retweet
 * @param mixed $names ids yang ingin di retweet
 * @param string $url url api twitter
 */
function tw_multi_rt($ids, $url) {
  $arrays = array();
  $urls = array();
  foreach($ids as $id) {
    $arrays[] = array();
    $urls[] = $url.'/'.$id;
  };
  return tw_multi_url_request($arrays, $urls, 'POST');
}

/**
 * Fungsi merubah data dari twitter ke data yang dibutuhkan untuk tampilan
 * @param mixed $user array of data
 */
function tw_render_list($list) {
  return array(
    $list['name'],
    $list['description'],
    $list['member_count'],
    $list['subscriber_count'],
    $list['uri'],
    $list['mode']
  );
}

/**
 * Fungsi merubah data dari twitter ke data yang dibutuhkan untuk tampilan
 * @param mixed $user array of data
 */
function tw_render_user($user, $json = false) {
  $user_description = $user['description'];
  $user_location = $user['location'];
  $user_folbek_ratio = $user['friends_count'] == 0? 0: number_format($user['followers_count']/$user['friends_count'], 3);
  if (isset($user['status'])) {
    $user_status_created_at = $user['status']['created_at'];
    $user_status_id_str = $user['status']['id_str'];
  } else {
    $user_status_created_at = '';
    $user_status_id_str = '';
  }
  $user_status_text = isset($user['status']) ? $user['status']['text'] : '';
  $action='00000';
  $twitter_time = $user_status_created_at == ''?'-':tw_time($user_status_created_at);
  if ($user_status_created_at == '') {
    $display_time = '';
    if ($user['protected']) {
      $twitter_time = 'Tweet Protected.';
      $filter_time = '01-01-1970';
      $sort_time = 0;
    } else {
      $twitter_time = 'Tidak ada data.';
      $filter_time = '01-02-1970';
      $sort_time = 1;
    }
  } else {
    $twitter_time = tw_time($user_status_created_at);
    $local_date = date_timezone_set(date_create($user_status_created_at),timezone_open('Asia/Jakarta'));
    $filter_time = date_format($local_date, 'd-m-Y H:i:sP');
    $display_time = date_format($local_date, DATE_RFC1123);
    $sort_time = strtotime($user_status_created_at) *1000;
  }
  if (isset($user['protected']) && $user['protected']==true) {
    $action[0]='1';
  }
  if ($user['default_profile_image']) {
    $action[1]='1';
  }
  if (isset($user['verified'])&& $user['verified']==true) {
    $action[2]='1';
  }
  if (strpos($user_location, 'ÜT: ') !== false) {
    $action[3]='1';
  }
  $user_url = '';
  if (isset($user['url'])) {
    $action[4]='1';
    $user_url = $user['entities']['url']['urls'][0]['expanded_url'];
    $action.= $user_url;
  }
  if (isset($user['entities']['description']['urls'][0])) {
    foreach ($user['entities']['description']['urls'] as $k => $v) {
      $url = $v['url'];
      $expanded_url = $v['expanded_url'];
      $user_description = str_replace($url, $expanded_url, $user_description);
    }
  }
  if (!$json) {
    if (isset($user['status']) && isset($user['status']['entities']) && isset($user['status']['entities']['urls'])) {
      $user_status_urls = $user['status']['entities']['urls'];
      foreach ($user_status_urls as $v) {
        $url = $v['url'];
        $expanded_url = $v['expanded_url'];
        $user_status_text = str_replace($url, $expanded_url, $user_status_text);
      }
    }
    $is_follower = tw_check_follower($user['id_str']);
    $is_following = tw_check_following($user['id_str']);
    return array(
      $user['profile_image_url'],
      $user['screen_name'],
      $user['name'],
      tw_link($user_description),
      $user_location,
      $user['followers_count'],
      $user['friends_count'],
      $user['statuses_count'],
      $user['listed_count'],
      //~ $twitter_time.'#'.$filter_time.'#'.$sort_time.'#'.$display_time.'#{{tweet}}'.tw_link($user_status_text),
      $twitter_time.'#'.$filter_time.'#'.$sort_time.'#'.$display_time,
      $user['verified']?1:0,
      $user['protected']?1:0,
      $user_folbek_ratio,
      $is_follower,
      $is_following,
      $action,
      tw_link($user_status_text),
      $user_status_id_str,
      $user['id_str']
    );
  } else {
    if (isset($user['status']) && isset($user['status']['entities']) && isset($user['status']['entities']['urls'])) {
      $user_status_urls = $user['status']['entities']['urls'];
      foreach ($user_status_urls as $v) {
        $url = $v['url'];
        $expanded_url = $v['expanded_url'];
        $user_status_text = str_replace($url, $expanded_url, $user_status_text);
      }
    }
    $data = array(
      'name' => $user['name'],
      'screen_name' => '<a href="http://twitter.com/'.$user['screen_name'].'" target="_blank">@'.$user['screen_name'].'</a>',
      'description' => tw_link($user_description),
      'status_text' => tw_link($user_status_text),
      'tweet_time' => $twitter_time,
      'raw tweet_time' => $filter_time,
      'statuses_count' => $user['statuses_count'],
      'followers_count' => $user['followers_count'],
      'friends_count' => $user['friends_count'],
      'listed_count' => $user['listed_count'],
      'location' => $user_location,
      'folbek_ratio' => $user_folbek_ratio,
      'verified' => $user['verified']?1:0,
      'protected' => $user['protected']?1:0,
      'default_profile_image' => $user['default_profile_image']?1:0,
      'url' => tw_link($user_url)
    );
    $img = array(
      'profile_image_url_https' => $user['profile_image_url']
    );
    $output = array(
      'user' => $data,
      'user_img'=> $img
    );
    return $output;
  }
}

/**
 * Fungsi merubah data dari twitter ke data yang dibutuhkan untuk tampilan
 * @param mixed $tweet array of data
 */
function tw_render_tweet($status, $json = false) {
  $user = $status['user'];
  $user_description = $user['description'];
  $user_location = $user['location'];
  $user_folbek_ratio = $user['friends_count'] == 0? 0: number_format($user['followers_count']/$user['friends_count'], 3);
  $user_status_created_at = $status['created_at'];
  $user_status_text = isset($user['status']) ? $user['status']['text'] : '';
  $action='00000';
  $twitter_time = $user_status_created_at == ''?'-':tw_time($user_status_created_at);
  if ($user_status_created_at == '') {
    $display_time = '';
    if ($user['protected']) {
      $twitter_time = 'Tweet Protected.';
      $filter_time = '1970-01-01';
      $sort_time = 0;
    } else {
      $twitter_time = 'Tidak ada data.';
      $filter_time = '1970-01-02';
      $sort_time = 1;
    }
  } else {

    $twitter_time = tw_time($user_status_created_at);
    $local_date = date_timezone_set(date_create($user_status_created_at),timezone_open('Asia/Jakarta'));
    $filter_time = date_format($local_date, 'd-m-Y H:i:sP');
    $display_time = date_format($local_date, DATE_RFC1123);
    $sort_time = strtotime($user_status_created_at) *1000;
  }
  if (isset($user['protected']) && $user['protected']==true) {
    $action[0]='1';
  }
  if ($user['default_profile_image']) {
    $action[1]='1';
  }
  if (isset($user['verified'])&& $user['verified']==true) {
    $action[2]='1';
  }
  if (strpos($user_location, 'ÜT: ') !== false) {
    $action[3]='1';
  }
  if (isset($user['url'])) {
    $action[4]='1';
    $action.=$user['entities']['url']['urls'][0]['expanded_url'];
  }
  if (isset($user['entities']['description']['urls'][0])) {
    foreach ($user['entities']['description']['urls'] as $k => $v) {
      $url = $v['url'];
      $expanded_url = $v['expanded_url'];
      $user_description = str_replace($url, $expanded_url, $user_description);
    }
  }

  $is_follower = tw_check_follower($user['id_str']);
  $is_following = tw_check_following($user['id_str']);
  $json_table = array(
    $user['profile_image_url'],
    $user['screen_name'],
    $user['name'],
    tw_link($status['text']),
    $twitter_time.'#'.$filter_time.'#'.$sort_time.'#'.$display_time,
    tw_link($user_description),
    $user_location,
    $user['followers_count'],
    $user['friends_count'],
    $user['statuses_count'],
    $user['listed_count'],
    $user['verified']?1:0,
    $user['protected']?1:0,
    $user_folbek_ratio,
    $is_follower,
    $is_following,
    $action,
    $status['id_str'],
    $user['id_str']
  );
  return $json_table;
}

/**
 * Fungsi memngubah kumpulan data dari twitter ke data yang dibutuhkan sebagai output
 * @param mixed $in array of names atau atribut masukan
 * @param mixed $resp array of response dari multi curl twitter
 */
function tw_response_act($in, $resps){
  $i = 0;
  $out = array(
    'success' => array(),
    'fail' => array(
      'name' => array(),
      'code' => array(),
      'total' => 0
    )
  );
  foreach ($resps as $resp) {
    if (is_null($resp)) {
      $out['fail']['name'][] = $in[$i];
      $out['fail']['code'][] = 0;
    } else if (isset($resp->errors)) {
      if (is_object($resp->errors[0])) {
        $out['fail']['code'][] = $resp->errors[0]->code;
      } else {
        $out['fail']['code'][] = $resp->errors;
      }
      $out['fail']['name'][] = $in[$i];
    } else if (isset($resp->response['headers']['status']) &&  $resp->response['headers']['status'] == '403 Forbidden') {
      $out['fail']['name'][] = $in[$i];
      $out['fail']['code'][] = 403;
    } else if (isset($resp->response['headers']['status']) &&  $resp->response['headers']['status'] == '404 Not Found'){
      $out['fail']['name'][] = $in[$i];
      $out['fail']['code'][] = 404;
    } else if (isset($resp->response['headers']['status']) &&  $resp->response['headers']['status'] == '200 OK'){
      $out['success'][] = $in[$i];
    } else if (isset($resp->id_str)){
      $out['success'][] = $resp->id_str;
    } else {
      echo '<pre>'.print_r($resp,1).'</pre>';exit();
    }
    $i++;
  }
  $out['fail']['total'] = count($out['fail']['code']);
  return $out;
}

/**
 * Fungsi mengecek hubungan follow/following id twitter
 * @param int $id id twitter
 * @param string $type follower|following
 */
function tw_check($id, $type) {
  if ( $type == 'follower') {
    $haystack = $_SESSION['follower'];
  } else if ($type == 'following') {
    $haystack = $_SESSION['friend'];
  } else {
    return false;
  }
  $flipped_hay = array_flip($haystack);
  return isset($flipped_hay[(string)$id]);
}

/**
 * Fungsi mengecek hubungan follower
 * @param int $id id
 */
function tw_check_follower($id) {
  return tw_check($id, 'follower');
}

/**
 * Fungsi mengecek hubungan following
 * @param int $id id
 */
function tw_check_following($id) {
  return tw_check($id, 'following');
}

/**
 * Fungsi mengambil data penjadwalan tweet
 * @param string $search jenis data penjadwalan tweet yang diinginkan
 * @param int $start cursor data pertama yang diinginkan
 */
function tw_jadwal($search, $start) {
  $output = array();
  $output['success'] = true;
  $resp = tw_get_jadwal($search, $start);
  $output['response'] = $resp['arr'];
  $output['count'] = $resp['count'];
  $output['cursor'] = 0;
  return $output;
}

/**
 * Fungsi mengambil data penjadwalan dari database
 * @param string $search jenis data
 * @param int $start cursor data pertama yang diinginkan
 */
function tw_get_jadwal($search, $start) {
  $limit = 20;
  switch ($search) {
    case 'penjadwalan_tweet';
      $type = 1;
      break;
    case 'tweet_berulang';
      $type = 2;
      break;
    case 'tweet_bergamber';
      $type = 3;
      break;
    default :
      $type = 1;
      break;
  }
  $start--;
  $arr = q("SELECT twitter_id, waktu, isi, gambar, id FROM ref_jadwal WHERE type='$type' AND user_id = ".$_SESSION['user_id']." AND aktif='yes' ORDER BY waktu ASC LIMIT $start, $limit");
  $count = q("SELECT count(id) as count FROM ref_jadwal WHERE type='$type' AND user_id = ".$_SESSION['user_id']." AND aktif='yes'");
  $output['arr'] = tw_render_jadwal($arr);
  $output['count'] = $count[0]['count'];
  return $output;
}

/**
 * Fungsi menambah data penjadwalan ke database by ID
 * @param int $id
 */
function tw_get_jadwal_by_id($id) {
  $arr = q("SELECT twitter_id, waktu, isi, gambar, id FROM ref_jadwal WHERE id = ".$id);
  $arr = tw_render_jadwal($arr);
  return $arr[0];
}

/**
 * Fungsi menambah data penjadwalan ke database by ID
 * @param int $id
 */
function tw_get_jadwal_by_id_arr($ids) {
  if ($ids) {
    $ids = implode(',', $ids);
    $arr = q("SELECT twitter_id, waktu, isi, gambar, id FROM ref_jadwal WHERE id IN (".$ids.")");
    $arr = tw_render_jadwal($arr);
    return $arr;
  }
  return false;
}

/**
 * Fungsi menyiapkan data penjadwalan untuk tabel
 * @param $arr array dari database
 */
function tw_render_jadwal($arr) {
  $twitter_ids = array();
  $res = q('SELECT twitter_id, twitter_screenname FROM ref_account WHERE user_id = "'.$_SESSION['user_id'].'"');
  foreach($res as $k => $v) {
    $twitter_ids[$v['twitter_id']] = $v['twitter_screenname'];
  }
  //~ $twitter_ids = implode(',', $twitter_ids);
  $output = array();
  if (!empty($arr)) {
    foreach ($arr as $k => $v) {
      $gambar = '';
      if (!empty($v['gambar'])) {
        $gambar = UPLOAD_URL.$v['gambar'];
      }
      $date = new DateTime($v['waktu']);
      $waktu = $date->format('d.m.Y H:i');
      $output[] = array($twitter_ids[$v['twitter_id']]."#".$v['id'], $waktu, tw_link($v['isi']), $gambar);
    }
  }
  return $output;
}

/**
 * Fungsi menambah data penjadwalan ke database
 * @param string $search jenis data
 */
function tw_add_jadwal($tweet) {
  global $link_ins;
  $date = new DateTime($tweet->jadwal, new DateTimeZone('Pacific/Nauru'));
  $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
  $jadwal =  $date->format('Y-m-d H:i:sP');
  $isi = mysqli_escape_string($link_ins, $tweet->isi);
  $gambar = mysqli_escape_string($link_ins, $tweet->gambar);
  $values = array();
  $twitter_ids = (array) $tweet->twitter_id;
  $count = count($twitter_ids);
  if ($count > 0) {
    foreach($twitter_ids as $k => $v) {
      $twitter_id = mysqli_escape_string($link_ins, $v);
      $values[] = '"'.$jadwal.'", "'.$isi.'", '.$_SESSION['user_id'].', '.$twitter_id.', "'.$gambar.'"';
    }
    }
  $query = 'INSERT INTO ref_jadwal (waktu, isi, user_id, twitter_id, gambar) VALUES ('.implode('),(' , $values).')';
  $ok = q($query);
  if ($ok) {
    $first_id = mysqli_insert_id($link_ins);
    $ids = array();
    while ($count > 0) {
      $ids[] = $first_id;
      $count--;
      $first_id++;
    }
  }
  return $ids;
}

/**
 * Fungsi menambah data penjadwalan ke database
 * @param string $search jenis data
 * @param bool $debug jenis data
 */
function tw_add_jadwal_arr($arr, $debug = false) {
  global $link_ins;
  $query = 'INSERT INTO ref_jadwal (waktu, isi, user_id, twitter_id) VALUES ';
  $user_id = $_SESSION['user_id'];
  $token = $_SESSION['oauth_token'];
  $twitter_id = explode('-', $token)[0];
  $twitter_ids = array($twitter_id);
  if(isset($_REQUEST['akun'])) {
    $twitter_ids = explode(',',$_REQUEST['akun']);
  }
  $count_ids = count($twitter_ids);
  $gambar = '';
  $values = array();
  $format = 'd/m/Y H:i';
  $gmt = new DateTimeZone('Pacific/Nauru');
  $jkt = new DateTimeZone('Asia/Jakarta');
  $count_arr = count($arr);
  $count = $count_ids * $count_arr;
  foreach($arr as $k => $tweet) {
    $date = DateTime::createFromFormat($format, $tweet[0], $gmt);
    if (!$date) {
      break;
    }
    $date->setTimezone($jkt);
    $jadwal =  $date->format('Y-m-d H:i:sP');
    $isi = mysqli_escape_string($link_ins, $tweet[1]);
    foreach($twitter_ids as $k => $twitter_id) {
      $values[] ="('$jadwal', '$isi', $user_id, $twitter_id)";
    }
  }
  if (empty($values)) {
    return false;
  }
  $queryValue = implode(',', $values);
  $query .= $queryValue;
  if($debug) {
    $ok = q($query, 1);
  } else {
    $ok = q($query);
  }
  if ($ok) {
    $first_id = mysqli_insert_id($link_ins);
    $ids = array();
    while ($count > 0) {
      $ids[] = $first_id;
      $count--;
      $first_id++;
    }
  }
  return $ids;
}

/**
 * Fungsi menambah data penjadwalan ke database
 * @param string $search jenis data
 */
function tw_edit_jadwal($tweet) {
  global $link_upd;
  $id = mysqli_escape_string($link_upd, $tweet->id);
  $date = new DateTime($tweet->jadwal, new DateTimeZone('Pacific/Nauru'));
  $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
  $jadwal =  $date->format('Y-m-d H:i:sP');
  $isi = mysqli_escape_string($link_upd, $tweet->isi);
  $twitter_id = mysqli_escape_string($link_upd, $tweet->twitter_id);
  $gambar = mysqli_escape_string($link_upd, $tweet->gambar);
  $gambarQuery = '';
  if(!empty($gambar)) {
    //~ todo hapus gambar lama
    $gambar_lama = $tweet->gambar_lama;
    $gambarQuery = ', gambar="'.$gambar.'"';
  }
  $query = 'SET @uids := null; UPDATE ref_jadwal SET waktu="'.$jadwal.'", isi="'.$isi.'", twitter_id = '.$twitter_id.$gambarQuery.' WHERE id='.$id.' AND ( SELECT @uids := CONCAT_WS(",", id, @uids) ); SELECT @uids;';
  $link = $link_upd;
  if (mysqli_multi_query($link, $query)) {
    do {
      if ($result = mysqli_store_result($link)) {
        while ($row = mysqli_fetch_row($result)) {
          $ok = $row[0];
        }
        mysqli_free_result($result);
      }
      if (!mysqli_more_results($link)) {
        break;
      }
    } while (mysqli_next_result($link));
  }
  mysqli_close($link);
  return $ok;
}

/**
 * Fungsi menambah data penjadwalan ke database by ID
 * @param int $id
 */
function tw_remove_jadwal($ids) {
  $sql = "SELECT gambar FROM ref_jadwal WHERE id IN ($ids)";
  $arr = q($sql);
  $images = array();
  foreach($arr as $k => $v) {
    $data = $v['gambar'];
    if (!empty($data)) {
      $img = UPLOAD_PATH.urldecode($data);
      $thumb = UPLOAD_PATH.urldecode(str_replace('/','/thumbnail/', $data));
      unlink($img);
      unlink($thumb);
      $folder = dirname($thumb);
      $folder2 = dirname($img);
      if (is_dir_empty($folder)) {
        rmdir($folder);
      }
      if (is_dir_empty($folder2)) {
        rmdir($folder2);
      }
    }
  }
  $sql = "DELETE FROM ref_jadwal WHERE id IN ($ids)";
  $arr = q($sql);
  return $arr;
}

/**
 * Fungsi menambah data penjadwalan ke database by ID
 * @param int $id
 */
function tw_deactive_jadwal($ids) {
  $sql = "UPDATE ref_jadwal SET aktif='no' WHERE id IN ($ids)";
  $arr = q($sql);
  return $arr;
}

function tw_add_berulang($data, $debug = false){
  $now = new DateTime();
  $start = new DateTime();
  $start->setTime($data->jam, $data->menit);
  if($now > $start) $start->add(new DateInterval('P1D'));

  $step = new DateInterval('PT'.$data->step.'M');
  $one_day = new DateInterval('P1D');

  $next = tw_date_step($start, $step);
  $besok = tw_date_step($start, $one_day);

  $tweets = $data->isi;
  $jumlah = $data->jumlah;
  $count_arr = count($data->isi);

  $user_id = $_SESSION['user_id'];
  $twitter_ids = (array) $data->twitter_id;
  $count_ids = count($twitter_ids);

  global $link_ins;
  $query = 'INSERT INTO ref_jadwal (waktu, isi, user_id, twitter_id) VALUES ';
  $values = array();
  $format = 'd/m/Y H:i';

  $gmt = new DateTimeZone('Pacific/Nauru');
  $jkt = new DateTimeZone('Asia/Jakarta');
  $count = $count_ids * $count_arr;
  $i = 0;

  foreach($tweets as $k => $tweet) {
    $jadwal =  $start->format('Y-m-d H:i:sP');
    $isi = mysqli_escape_string($link_ins, $tweet);
    foreach($twitter_ids as $k => $twitter_id) {
      $values[] ="('$jadwal', '$isi', $user_id, $twitter_id)";
    }
    if(++$i == $jumlah) {
      $i = 0;
      $start->add($one_day);
      $start->setTime($data->jam, $data->menit);
    } else {
      $start->add($step);
    }
  }
  if (empty($values)) {
    return false;
  }
  $queryValue = implode(',', $values);
  $query .= $queryValue;
  if($debug) {
    $ok = q($query, 1);
  } else {
    $ok = q($query);
  }
  if ($ok) {
    $first_id = mysqli_insert_id($link_ins);
    $ids = array();
    while ($count > 0) {
      $ids[] = $first_id;
      $count--;
      $first_id++;
    }
  }
  return $ids;
}

function tw_date_step($start, $step) {
  $next = clone $start;
  return $next->add($step);
}

/**
 * Fungsi mengambil data penjadwalan tweet
 * @param string $search jenis data penjadwalan tweet yang diinginkan
 * @param int $start cursor data pertama yang diinginkan
 */
function tw_kultweet($search, $start) {
  $output = array();
  $output['success'] = true;
  $output['response'] = tw_get_kultweet($search, $start);
  $output['count'] = count($output['response']);
  $output['cursor'] = 0;
  return $output;
}

/**
 * Fungsi mengambil data penjadwalan dari database
 * @param string $search jenis data
 * @param int $start cursor data pertama yang diinginkan
 */
function tw_get_kultweet($search, $start) {
  $start--;
  $arr = q("SELECT topik, deskripsi, hashtag, posisi, isi, orderby, twitter_id, id FROM ref_kultweet WHERE user_id = ".$_SESSION['user_id']." AND status='draft' LIMIT $start, $_SESSION[limit]");
  $output = tw_render_kultweet($arr);
  return $output;
}

/**
 * Fungsi menambah data penjadwalan ke database by ID
 * @param int $id
 */
function tw_get_kultweet_by_id($id) {
  $arr = q("SELECT topik, deskripsi, hashtag, isi, twitter_id, id, orderby, posisi FROM ref_kultweet WHERE id = ".$id);
  $arr = tw_render_kultweet($arr);
  return $arr[0];
}

/**
 * Fungsi menambah data penjadwalan ke database by ID
 * @param int $id
 */
function tw_get_kultweet_by_id_arr($ids) {
  if ($ids) {
    $ids = implode(',', $ids);
    $arr = q("SELECT topik, deskripsi, hashtag, isi, twitter_id, id, orderby, posisi FROM ref_kultweet WHERE id IN (".$ids.")");
    $arr = tw_render_kultweet($arr);
    return $arr;
  }
  return false;
}

/**
 * Fungsi menyiapkan data penjadwalan untuk tabel
 * @param $arr array dari database
 */
function tw_render_kultweet($arr) {
  $twitter_ids = array();
  $res = q('SELECT twitter_id, twitter_screenname FROM ref_account WHERE user_id = "'.$_SESSION['user_id'].'"');
  foreach($res as $k => $v) {
    $twitter_ids[$v['twitter_id']] = $v['twitter_screenname'];
  }
  $output = array();
  if (!empty($arr)) {
    foreach ($arr as $k => $v) {
      $ids = explode('/', $v['twitter_id']);
      $names = array();
      foreach($ids as $id) {
        if(!empty($id)) $names[] = $twitter_ids[$id];
      }
      $ids = implode('/',$names);
      if(empty($v['isi'])) {
        $count = 0;
      } else {
        $exp = explode('###',$v['isi']);
        $count = count($exp);
      }
      $output[] = array($ids."#".$v['id'], $v['topik'].'#'.$v['deskripsi'], $v['hashtag'].'#'.$v['posisi'], $v['isi']);
    }
  }
  return $output;
}

/**
 * Fungsi menambah data penjadwalan ke database
 * @param string $search jenis data
 */
function tw_add_kultweet($tweet) {
  global $link_ins;
  $tweet = (array) $tweet;
  foreach ($tweet as $k => $v) {
    $$k = $v;
    if (is_array($v)) continue;
    $$k = mysqli_real_escape_string($link_ins, $$k);
  }
  $hashtags = trim($hashtag, '#');
  $id = $_SESSION['user_id'];
  $twitter_ids = (array) $akun;
  $twitter_ids = implode('/', $twitter_ids);
  $query = "INSERT INTO ref_kultweet (user_id, twitter_id, topik, deskripsi, hashtag, status) VALUES  ($id, '$twitter_ids', '$topik', '$deskripsi', '$hashtag', 'draft')";
  $ok = q($query);
  if ($ok) {
    $first_id = mysqli_insert_id($link_ins);
  }
  return (array) $first_id;
}

/**
 * Fungsi menambah data penjadwalan ke database
 * @param string $search jenis data
 */
function tw_edit_kultweet($tweet) {
  $v = (array) $tweet;
  $id = $v['id'];
  $topik = $v['topik'];
  $deskripsi = $v['deskripsi'];
  $hashtag = str_replace('#', '', $v['hashtag']);
  $posisi = $v['posisi'];
  $twitter_id = implode('/',$v['twitter_id']);
  $isi = implode('###',$v['isi']);
  $posisi = $v['posisi'];
  $orderby = $v['orderby'];
  global $link_upd;
  $query = 'SET @uids := null; UPDATE ref_kultweet SET topik="'.$topik.'", deskripsi="'.$deskripsi.'", hashtag="'.$hashtag.'", posisi="'.$posisi.'", orderby="'.$orderby.'", isi="'.$isi.'", twitter_id = "'.$twitter_id.'" WHERE id='.$id.' AND ( SELECT @uids := CONCAT_WS(",", id, @uids) ); SELECT @uids;';
  $link = $link_upd;
  if (mysqli_multi_query($link, $query)) {
    do {
      if ($result = mysqli_store_result($link)) {
        while ($row = mysqli_fetch_row($result)) {
          $ok = $row[0];
        }
        mysqli_free_result($result);
      }
      if (!mysqli_more_results($link)) {
        break;
      }
    } while (mysqli_next_result($link));
  }
  mysqli_close($link);
  return $ok;
}

/**
 * Fungsi menambah data penjadwalan ke database by ID
 * @param int $id
 */
function tw_remove_kultweet($ids) {
  $sql = "DELETE FROM ref_kultweet WHERE id IN ($ids)";
  $arr = q($sql);
  return $arr;
}

/**
 * Fungsi menambah data penjadwalan ke database by ID
 * @param int $id
 */
function tw_deactive_kultweet($ids) {
  $sql = "UPDATE ref_kultweet SET status='sent' WHERE id IN ($ids)";
  $arr = q($sql);
  return $arr;
}

/**
 * Fungsi mengambil data log activity user
 */
function tw_history(){
  $history = array();
  //~ $history['spark']['follow'] = '100,200,140,250,220,110,310,240,90';
  //~ $history['spark']['unfollow'] = '100,200,140,250,220,110,310,240,90';
  //~ $history['spark']['forceunfollow'] = '100,200,140,250,220,110,310,240,90';
  $history['today']['follow'] = '0';
  $history['today']['unfollow'] = '0';
  $history['today']['forceunfollow'] = '0';
  return $history;
}

/**
 * Fungsi merubah string menjadi link, mendeteksi url pattern, hashtag dan mention
 * @param string $text text masukan
 * @TODO merubah link menjadi $_GET ke folbek.com
 */
function tw_link($text) {
  $text = preg_replace("/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", "<a href=\"\\0\" target=\'_blank\'>\\0</a>", $text);
  $text = preg_replace("/\B@(\w+(?!\/))\b/i", '<a href="https://twitter.com/\\1" target=\"_blank\">@\\1</a>', $text);
  $text = preg_replace("/\B(?<![=\/])#([\w]+[a-z]+([0-9]+)?)/i",'<a href="https://twitter.com/#!/search/%23\\1" target=\"_blank\">#\\1</a>', $text);
  return $text;
}

/**
 * fungsi merubah dari ATOM / ISO ke literal dalam bahasa Indonesia
 * @param string $time waktu / format date
 */
function tw_time($time) {
  $delta = time() - strtotime($time);
  if ($delta < -(48 * 60 * 60)) {
    return number_format(floor(abs($delta) / 86400)) . ' hari yang akan datang';
  } else if ($delta < -(120 * 60)) {
    return floor(abs($delta) / 3600) . ' jam yang akan datang';
  } else if ($delta < -(60 * 60)) {
    return floor(abs($delta) / 3600) . ' jam yang akan datang';
  } else if ($delta < -120) {
    return floor(abs($delta) / 60) . ' menit yang akan datang';
  } else if ($delta < 60) {
    return 'beberapa detik yang lalu';
  } else if ($delta < 120) {
    return 'semenit yang lalu';
  } else if ($delta < (60 * 60)) {
    return floor($delta / 60) . ' menit yang lalu';
  } else if ($delta < (120 * 60)) {
    return '1 jam yang lalu';
  } else if ($delta < (24 * 60 * 60)) {
    return floor($delta / 3600) . ' jam yang lalu';
  } else if ($delta < (48 * 60 * 60)) {
    return '1 hari yang lalu';
  } else {
    return number_format(floor($delta / 86400)) . ' hari yang lalu';
  }
}


function is_dir_empty($dir) {
  if (!is_readable($dir)) return NULL;
  $handle = opendir($dir);
  while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != "..") {
      return FALSE;
    }
  }
  return TRUE;
}