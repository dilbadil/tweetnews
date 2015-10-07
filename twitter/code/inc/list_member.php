<?php
$json_table = array();
$kurang = $_SESSION['limit'];
do {
  $currentTotal = 0;
  $output = tw_list_member($search, $page);
  if ($page == 1) $page = 'next';
  if ($output['success']) {
    $_SESSION[$search.'list_member_next_cursor'] = $output['next'];
    $_SESSION[$search.'list_member_previous_cursor'] = $output['previous'];
    $currentTotal = count($output['response']);
    $json_table = array_merge($output['response'], $json_table);
    $last = $output['next'] == 0? 1: 0;
    $first = $output['previous'] == 0? 1: 0;
    if ($page == 'next' && $last == 1) break;
    if ($page == 'previous' && $first == 1) break;
    $kurang = $kurang - $currentTotal;
  }
} while ($kurang > 0 && $currentTotal == 20 && $output['success']);
if ($output['success']) {
  $total = count($json_table);
  $response = array (
    'data' => $json_table,
    'iCurrentTotalRecord' => $total,
    'bFirstPage' => $first,
    'bLastPage' => $last
  );
}