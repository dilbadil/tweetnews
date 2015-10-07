<?php
$start = max((($page-1) * $limit + 1),1);
$json_table = array();
//~ jika sudah pernah di cari / sudah ada di session
if ($handle == 1 && isset($_SESSION['balas_budi'])) $handle = 2;
if ($handle == 3 && isset($_SESSION['balas_budi']) && $start > count($_SESSION['balas_budi'])) $handle = 2;
if ($handle == 1) {
  $output = tw_user($search);
  if ($output['success']) {
    $data_user = $output['response'];
    $data_count = $data_user['followers_count'] > 5000? 5000: $data_user['followers_count'];
    $output = tw_followers_ids($search, $data_count);
    if ($output['success']) {
      $data_follower_ids = $output['response'];
      $_SESSION['balas_budi'] = $data_follower_ids['ids'];
      $_SESSION[$search.'_follower_cursor'] = $data_follower_ids['next_cursor_str'];
      $output = tw_multi_user($search, $start, 'balas_budi');
      if ($output['success']) {
        $data = $output['response'];
        foreach ($data as $user) {
          $json_table[] = $user;
        }
        $total = count($_SESSION['balas_budi']);
        $end = min(($start + $limit - 1), $total);
        $response = array (
          'user' => $data_user,
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
} else if ($handle == 2) {
  $output = tw_multi_user($search, $start, 'balas_budi');
  if ($output['success']) {
    $data = $output['response'];
    foreach ($data as $user) {
      $json_table[] = $user;
    }
    $total = count($_SESSION['balas_budi']);
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
    $_SESSION['balas_budi'] = array_merge($_SESSION['balas_budi'], $data_follower_ids['ids']);
    $_SESSION['balas_budi'] = array_slice($_SESSION['balas_budi'], 0, MAX_RET_DATA);
    $_SESSION[$search.'_follower_cursor'] = $data_follower_ids['next_cursor_str'];
    $output = tw_multi_user($search, $start, 'balas_budi');
    if ($output['success']) {
      $data = $output['response'];
      foreach ($data as $user) {
        $json_table[] = $user;
      }
      $total = count($_SESSION['balas_budi']);
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
    $output = tw_multi_user($search, $start, 'balas_budi');
    if ($output['success']) {
      $json_table = array();
      $data = $output['response'];
      foreach ($data as $user) {
        $json_table[] = $user;
      }
      $total = count($_SESSION['balas_budi']);
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