<?php
$search = urlencode($search);
$count = 0;
$http_code = "";
while ($http_code != 200 || !empty($error))
{
  $connection->request('GET', $connection->url('1.1/search/tweets'),
    array(
    'q' => $search,
    'result_type' => 'recent',
    'count' => $limit,
    'include_entities' => false,
    'max_id' => $_SESSION['max_id']
  ));
  $http_code = $connection->response['code'];
  $error =$connection->response['error'];
  if (++$count == 5)
  {
    break;
  }
}
if ($http_code == 200 && empty($error)) {
  $data = json_decode($connection->response['response'], true);
$data_search = $data['search_metadata'];
  $next_id = 0;
  if (isset($data_search['next_results'])) {
    $next = explode('&',$data_search['next_results']);
    $next1 = explode('=', $next[0]);
    $next_id = $next1[1];
    $_SESSION['max_id'] = $next_id;
  }
  $statuses = $data['statuses'];
  foreach ($statuses as $status) {
    $user = $status['user'];
    $user_status_created_at = $status['created_at'];
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
      linkify($status['text']),
      $twitter_time,
      $user['location'],
      $user['followers_count'],
      $user['friends_count'],
      $user['statuses_count'],
      $user['listed_count'],
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
  $output = array (
    'search_data' => $data_search,
    'data' => $json_table,
    'limit' => $limit,
    'current' => 1,
    'next' => $next_id
  );
  echo json_encode($output);
  exit();
}
$output['error'] = json_decode($connection->response['error'], true);
$output['json_table'] = array();