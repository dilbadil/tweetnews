<?php
require_once('../../settingan/ketetapan.php');

    $screen_name=$_POST['screen_name'];
    require(LIB_PATH.'oauth_twitter/config.php');
    require(LIB_PATH.'oauth_twitter/oauth_lib.php');
    $connection = get_connection(array('user_token'=>$_SESSION['oauth_token'],'user_secret'=>$_SESSION['oauth_token_secret']));
    $connection->request('GET', $connection->url('1.1/friends/list'),
      array('screen_name' => $screen_name,
      'cursor' => -1,
      'count' => 200,
      'skip_status' => true,
      'include_user_entities' => false
    ));
    $http_code = $connection->response['code'];
    if ($http_code == 200) {
      $data = json_decode($connection->response['response'], true);
      $users = $data['users'];
      $cursor = $data['next_cursor_str'];
      $dt['data_table']='';
      $json_table=array();
      if (sizeof($users)) {
        foreach($users as $user) {
          $badge ='';
          if ($user['following']==true)
          {
            $badge.='<span>followed </span> ';
          }
          else
          {
            $badge.='<button type="button" class="follow">follow</button> ';
          }
          if ($user['protected']==true)
          {
            $badge.='<a href="javascript:void(0)">protected</a> ';
          }
          if ($user['default_profile_image'])
          {
            $badge.='<a class="telor" href="javascript:void(0)">telor</a> ';
          }
          if (isset($user['url']))
          {
            $badge.='<a href="'.$user['url'].'">visit site</a> ';
          }
          $json_table[]=array(
            '<img src="'.$user['profile_image_url'].'" alt="" title="" /><a href="http://twitter.com/'.$user['screen_name'].'" target="_blank">@'.$user['screen_name'].'</a>',
            $badge,
            $user['name'],
            $user['description'],
            $user['location']
          );
        }
      } else {
        $cursor = 0;
      }
    } else {
      $data = json_decode($connection->response['response'], true);
    }
    $dt['screen_name']='Showing '.count($json_table).' of <span class="jp_friends_count">-</span> @'.$screen_name.'\'s Follower.';
    $dt['json_table']=$json_table;
    echo json_encode($dt);

?>