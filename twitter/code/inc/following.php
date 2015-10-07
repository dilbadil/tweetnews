<?php
$start = max((($page-1) * $limit + 1),1);
$json_table = array();
//~ jika sudah pernah di cari / sudah ada di session
if ($handle == 1 && isset($_SESSION[$search.'_friend_ids'])) $handle = 2;
if ($handle == 3 && isset($_SESSION[$search.'_friend_ids']) && $start > count($_SESSION[$search.'_friend_ids'])) $handle = 2;
if ($handle == 1) {
  $output = tw_user($search);
  if ($output['success']) {
    $data_user = $output['response'];
    $data_count = $data_user['friends_count'] > 5000? 5000: $data_user['friends_count'];
    $output = tw_friends_ids($search, $data_count);
    if ($output['success']) {
      $data_friend_ids = $output['response'];
      $_SESSION[$search.'_friend_ids'] = $data_friend_ids['ids'];
      $_SESSION[$search.'_friend_cursor'] = $data_friend_ids['next_cursor_str'];
      $output = tw_multi_user($search, $start, 'friend_ids');
      if ($output['success']) {
        $data = $output['response'];
        foreach ($data as $user) {
          $json_table[] = $user;
        }
        $total = count($_SESSION[$search.'_friend_ids']);
        $end = min(($start + $limit - 1), $total);
        $response = array (
          'user' => tw_render_user($data_user, true),
          'data' => $json_table,
          'iStart' => $start,
          'iEnd' => $end,
          'iTotalRecord' => $total,
          'iCurrentPage' => $page,
          'iTotalPage' => ceil($total/$limit)
        );
      }
    }
  }
  if (!$output['success']){
    $status = isset($output['http_code'])?$output['http_code']:400;
    $msg = isset($output['error']['message'])?$output['error']['message']:"Unknown Error.";
    tw_err($status,$msg);
  }
} elseif ($handle == 2) {
  $output = tw_multi_user($search, $start, 'friend_ids');
  if ($output['success']) {
    $data = $output['response'];
    foreach ($data as $user) {
      $json_table[] = $user;
    }
    $total = count($_SESSION[$search.'_friend_ids']);
    $end = min(($start + $limit - 1), $total);
    $response = array (
      'data' => $json_table,
      'iStart' => $start,
      'iEnd' => $end,
      'iTotalRecord' => $total,
      'iCurrentPage' => $page,
      'iTotalPage' => ceil($total/$limit)
    );
  }
} elseif ($handle == 3) {
  $start = max((($page-1) * $limit + 1),1);
  $output = tw_friends_ids($search, 5000, 3);
  if ($output['success']) {
    $data_friend_ids = $output['response'];
    $_SESSION[$search.'_friend_ids'] = array_merge($_SESSION[$search.'_friend_ids'], $data_friend_ids['ids']);
    $_SESSION[$search.'_friend_ids'] = array_slice($_SESSION[$search.'_friend_ids'], 0, MAX_RET_DATA);
    $_SESSION[$search.'_friend_cursor'] = $data_friend_ids['next_cursor_str'];
    $output = tw_multi_user($search, $start, 'friend_ids');
    if ($output['success']) {
      $data = $output['response'];
      foreach ($data as $user) {
        $json_table[] = $user;
      }
      $total = count($_SESSION[$search.'_friend_ids']);
      $end = min(($start + $limit - 1), $total);
      $response = array (
        'data' => $json_table,
        'iStart' => $start,
        'iEnd' => $end,
        'iTotalRecord' => $total,
        'iCurrentPage' => $page,
        'iTotalPage' => ceil($total/$limit)
      );
    }
    //~ kalau friend habis
  } else {
    $output = tw_multi_user($search, $start, 'friend_ids');
    if ($output['success']) {
      $json_table = array();
      $data = $output['response'];
      foreach ($data as $user) {
        $json_table[] = $user;
      }
      $total = count($_SESSION[$search.'_friend_ids']);
      $end = min(($start + $limit - 1), $total);
      $response = array (
        'data' => $json_table,
        'iStart' => $start,
        'iEnd' => $end,
        'iTotalRecord' => $total,
        'iCurrentPage' => $page,
        'iTotalPage' => ceil($total/$limit)
      );
    }
  }
}