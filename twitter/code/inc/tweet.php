<?php
//~ $search = urlencode($search);
$json_table = array();
$kurang = $_SESSION['limit'];
//~ jika output kurang dari 0.7 limit, asumsi data habis
do {
  $currentTotal = 0;
  $minimal = floor(min($kurang, 100) * 0.7);
  $output = tw_tweet_search($search, $page, $kurang + 10);
  if ($output['success']) {
    $_SESSION[$search.'tweet_search_next_cursor'] = $output['next'];
    $_SESSION[$search.'tweet_search_previous_cursor'] = $output['previous'];
    $json_table = array_merge($output['response'], $json_table);
    $currentTotal = count($output['response']);
    $kurang = $kurang - $currentTotal;
  }
  if ($page == 1) $page = 'next';
} while ($kurang > 0 && $currentTotal > $minimal && $output['success']);
if ($output['success']) {
  $last = $first = 0;
  if ($currentTotal < $minimal) {
    switch ($page) {
      case 1;
        $last = $first = 1;
        break;
      case 'next';
        $last = 1;
        break;
      case 'previous';
        $first = 1;
        break;
    }
  }
  $limit = $_SESSION['limit'];
  array_splice($json_table, $limit);
  $total = count($json_table);
  $response = array (
    'data' => $json_table,
    'iCurrentTotalRecord' => $total,
    'bFirstPage' => $first,
    'bLastPage' => $last
  );
}