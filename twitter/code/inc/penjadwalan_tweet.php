<?php
$limit = 20;
$start = max((($page-1) * $limit + 1),1);
$output = tw_jadwal($search, $start);
if ($output['success']) {
  $_SESSION[$search.'_cursor'] = $output['cursor'];
  $json_table = $output['response'];
  $total = $output['count'];
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