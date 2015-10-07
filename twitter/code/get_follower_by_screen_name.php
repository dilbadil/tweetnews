<?php
require_once('../../settingan/ketetapan.php');

  if (isset($_POST['screen_name']))
  {
    $screen_name = $_POST['screen_name'];
    $cursor = -1;
  } elseif(isset($_GET['screen_name'])) {
    $screen_name=$_GET['screen_name'];
    $cursor=$_GET['cursor'];
  } else {
    die(0);
  }
    require(LIB_PATH.'oauth_twitter/config.php');
    require(LIB_PATH.'oauth_twitter/oauth_lib.php');
    $connection = get_connection(array('user_token'=>$_SESSION['oauth_token'],'user_secret'=>$_SESSION['oauth_token_secret']));
    $count = 0;
    $http_code = "";
    while ($http_code != 200 || !empty($error))
    {
      $connection->request('GET', $connection->url('1.1/followers/list'),
        array('screen_name' => $screen_name,
        'cursor' => $cursor,
        'count' => 10,
        'include_user_entities' => false
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
      $users = $data['users'];
      $cursor = $data['next_cursor_str'];
      $json_table=array();
      if (sizeof($users)) {
        foreach($users as $user) {
          $user_status_created_at = isset($user['status']) ? $user['status']['created_at'] : '';
          $twitter_time = $user_status_created_at == ''?'-':twitter_time($user_status_created_at);
          $action='000';
          $badge ='<div class="btn-group">';
          if ($user['following']==true)
          {
            $badge.='<span>followed </span> ';
          }
          else
          {
            $badge.='<button class="jp-btn btn btn-primary follow"><i class="glyphicon-user_add"></i></button> ';
          }
          $badge.='<button class="jp-btn btn btn-primary unfollow" title="unfollow user"><i class="glyphicon-user_remove"></i></button> <button class="jp-btn btn btn-primary force_unfollow" title="force unfollow user"><i class="icon-remove-sign"></i></button></div><div class="btn-group"> ';
          if ($user['protected']==true)
          {
            $action[0]='1';
            $badge.='<button class="jp-btn btn btn-primary"><i class="icon-lock" title="this account is protected"></i></button> ';
          }
          if ($user['default_profile_image'])
          {
            $action[1]='1';
            $badge.='<button class="jp-btn btn btn-primary"><i class="glyphicon-picture" title="this account is using default profile picture"></i></button> ';
          }
          if (isset($user['url']))
          {
            $action[2]='1';
            $action.=$user['url'];
            $badge.='<a href="'.$user['url'].'" target="_blank"><button class="jp-btn btn btn-primary" title="visit site"><i class="icon-external-link"></i></button></a> ';
          }
          $badge.="</div>";
          $json_table[]=array(
            $user['profile_image_url'],
            $user['screen_name'],
            $user['name'],
            linkify($user['description']),
            $user['location'],
            jp_num_format($user['followers_count']),
            jp_num_format($user['friends_count']),
            jp_num_format($user['statuses_count']),
            jp_num_format($user['listed_count']),
            $twitter_time,
            $user['verified']?1:0,
            $user['protected']?1:0,
            jp_num_format_dec($user['followers_count']/$user['friends_count']),
            'unknown',
            $user['followers_count'],
            $user['friends_count'],
            $user['statuses_count'],
            $user['listed_count'],
            $user_status_created_at,
            $user['followers_count']/$user['friends_count'],
            $action
          );
        }
      } else {
        $cursor = 0;
      }
    } else {
      $dt['error'] = json_decode($connection->response['error'], true);
      $dt['json_table'] = array();
      echo json_encode($dt);
      exit();
    }
    $dt['screen_name']=$screen_name;
    $dt['json_table']=$json_table;
    $temp = array(
      'cursor' => $cursor,
      'total' => count($json_table),
      'screen_name' => $screen_name
    );
    $dt['temp'] = $temp;
    echo json_encode($dt);
?>