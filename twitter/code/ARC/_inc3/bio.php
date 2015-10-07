<?php
$page = 1;
if (isset($_GET['page'])) {
  $page = $_GET['page'];
}
$count = 0;
$http_code = "";
while ($http_code != 200 || !empty($error))
{
  $connection->request('GET', $connection->url('1.1/users/search'),
    array(
    'q' => $search,
    'count' => 20,
    'include_entities' => false,
    'page' => $page
  ));
  $http_code = $connection->response['code'];
  $error =$connection->response['error'];
  if (++$count == 5)
  {
    break;
  }
}
if ($http_code == 200 && empty($error)) {
  $users = json_decode($connection->response['response'], true);
  foreach ($users as $user) {
    $user_status_created_at = isset($user['status']) ? $user['status']['created_at'] : '';
    $twitter_time = $user_status_created_at == ''?'-':twitter_time($user_status_created_at);
    $action='000';
    if ($user['protected']==true)
    {
      $action[0]='1';
    }
    if ($user['default_profile_image'])
    {
      $action[1]='1';
    }
    if (isset($user['url']))
    {
      $action[2]='1';
      $action.=$user['url'];
    }
    $json_table[]=array(
      $user['profile_image_url'],
      $user['screen_name'],
      $user['name'],
      linkify($user['description']),
      $user['location'],
      $user['followers_count'],
      $user['friends_count'],
      $user['statuses_count'],
      $user['listed_count'],
      $twitter_time,
      $user['verified']?1:0,
      $user['protected']?1:0,
      $user['friends_count']==0?0:number_format($user['followers_count']/$user['friends_count'], 3)*1000,
      'unknown',
      $user['followers_count'],
      $user['friends_count'],
      $user['statuses_count'],
      $user['listed_count'],
      $user_status_created_at,
      $user['friends_count']==0?0:$user['followers_count']/$user['friends_count'],
      $action
    );
  }
  $currentTotal =  count($json_table);
  if (count($json_table) == $limit) {
    $currentTotal += 1;
  }
  $output = array (
    'data' => $json_table,
    'limit' => $limit,
    'current' => $page,
    'page' => $page,
    'currentTotal' => $currentTotal
  );
  echo json_encode($output);
  exit();
}
$output['error'] = json_decode($connection->response['error'], true);
$output['json_table'] = array();