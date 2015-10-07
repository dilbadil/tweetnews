<?php
$json_table = array();
$kurang = $_SESSION['limit'];
do {
  $currentTotal = 0;
  $output = tw_bio_search($search, $page);
  if ($output['success']) {
    $_SESSION[$search.'_bio_search_page'] = $output['page'];
    if ($page == 'previous') {
      $_SESSION[$search.'_bio_search_page_cursor'] = $output['last_user_id'];
    }
    $json_table = array_merge($output['response'], $json_table);
    $currentTotal = count($output['response']);
    $kurang = $kurang - $currentTotal;
  }
  if ($page == 1) $page = 'next';
} while ($kurang > 0 && $currentTotal == 20 && $output['success']);
if ($output['success']) {
  $total = count($json_table);
  $limit = $_SESSION['limit'];
  $last = $total < $limit? 1: 0;
  $first = $output['page'] == 1? 1: 0;
  $response = array (
    'data' => $json_table,
    'iCurrentTotalRecord' => $total,
    'bFirstPage' => $first,
    'bLastPage' => $last
  );
}