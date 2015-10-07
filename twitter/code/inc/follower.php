<?php
$start = max((($page-1) * $limit + 1),1);
$json_table = array();
//~ jika sudah pernah di cari / sudah ada di session
if ($handle == 1 && isset($_SESSION[$search.'_follower_ids'])) $handle = 2;
if ($handle == 3 && isset($_SESSION[$search.'_follower_ids']) && $start > count($_SESSION[$search.'_follower_ids'])) $handle = 2;
if ($handle == 1) {
  $output = tw_user($search);
  if ($output['success']) {
    $data_user = $output['response'];
    $data_count = $data_user['followers_count'] > 5000? 5000: $data_user['followers_count'];
    $output = tw_followers_ids($search, $data_count);
    if ($output['success']) {
      $data_follower_ids = $output['response'];
      $_SESSION[$search.'_follower_ids'] = $data_follower_ids['ids'];
      $_SESSION[$search.'_follower_cursor'] = $data_follower_ids['next_cursor_str'];
      $output = tw_multi_user($search, $start);
      if ($output['success']) {
        $data = $output['response'];
        foreach ($data as $user) {
          $json_table[] = $user;
        }
        $total = count($_SESSION[$search.'_follower_ids']);
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
  $output = tw_multi_user($search, $start);
  if ($output['success']) {
    $data = $output['response'];
    foreach ($data as $user) {
      $json_table[] = $user;
    }
    $total = count($_SESSION[$search.'_follower_ids']);
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
  $output = tw_followers_ids($search, 5000, 3);
  if ($output['success']) {
    $data_follower_ids = $output['response'];
    $_SESSION[$search.'_follower_ids'] = array_merge($_SESSION[$search.'_follower_ids'], $data_follower_ids['ids']);
    $_SESSION[$search.'_follower_ids'] = array_slice($_SESSION[$search.'_follower_ids'], 0, MAX_RET_DATA);
    $_SESSION[$search.'_follower_cursor'] = $data_follower_ids['next_cursor_str'];
    $output = tw_multi_user($search, $start);
    if ($output['success']) {
      $data = $output['response'];
      foreach ($data as $user) {
        $json_table[] = $user;
      }
      $total = count($_SESSION[$search.'_follower_ids']);
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
    //~ kalau follower habis
  } else {
    $output = tw_multi_user($search, $start);
    if ($output['success']) {
      $json_table = array();
      $data = $output['response'];
      foreach ($data as $user) {
        $json_table[] = $user;
      }
      $total = count($_SESSION[$search.'_follower_ids']);
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