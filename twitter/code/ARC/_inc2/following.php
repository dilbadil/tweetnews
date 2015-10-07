<?php
$count = 0;
$http_code = "";
while ($http_code != 200 || !empty($error))
{
  $connection->request('GET', $connection->url('1.1/users/lookup'),
    array(
    'cursor' => -1,
    'include_entities' => false,
    'user_id' => array_slice($_SESSION[$search.'following_ids'], $start, $limit)
  ));
  $http_code = $connection->response['code'];
  $error =$connection->response['error'];
  if (++$count == 5)
  {
    break;
  }
}
$dt=array();
if ($http_code == 200 && empty($error)) {
  $data = json_decode($connection->response['response'], true);
  foreach ($data as $user) {
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
}
$output = array (
  'data' => $json_table,
  'page' => $page,
  'limit' => $limit
);