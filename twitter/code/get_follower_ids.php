<?php
require_once('../../../settingan/ketetapan.php');

    $screen_name=$_POST['screen_name'];
    $dt['screen_name']='Search @'.$screen_name.' Follower.';
    require(LIB_PATH.'oauth_twitter/config.php');
    require(LIB_PATH.'oauth_twitter/oauth_lib.php');
    $connection = get_connection(array('user_token'=>$_SESSION['oauth_token'],'user_secret'=>$_SESSION['oauth_token_secret']));
    $connection->request('GET', $connection->url('1.1/followers/list'),
      array('screen_name' => $screen_name,
      'cursor' => -1,
      'count' => 100,
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
        $class='odd';
        foreach($users as $user) {
          $badge ='';
          if ($user['following']==true)
          {
            $badge.='<a href="javascript:void(0)">mutual friend</a>';
          }
          else
          {
            $badge.='<a href="https://twitter.com/intent/follow?original_referer=&region=follow_link&screen_name='.$user['screen_name'].'&tw_p=followbutton&variant=2.0">follow</a>';
          }
          if ($user['protected']==true)
          {
            $badge.='<a href="javascript:void(0)">protected</a>';
          }
          if ($user['default_profile_image'])
          {
            $badge.='<a class="telor" href="javascript:void(0)">telor</a>';
          }
          if (isset($user['url']))
          {
            $badge.='<a href="'.$user['url'].'">visit site</a>';
          }
          $dt['data_table'].='
            <tr class="'.$class.'">
              <td class=""><img src="'.$user['profile_image_url'].'" alt="" title="" /><a href="http://twitter.com/'.$user['screen_name'].'" target="_blank">@'.$user['screen_name'].'</a></td>
              <td class="hidden-350 ">'.$user['name'].'</td>
              <td class="hidden-1024 ">'.$user['description'].'</td>
              <td class="hidden-480 ">'.$badge.'</td>
              <td class=" sorting_1"></td>
            </tr>
          ';
          $json_table[]=array(
            "<img src=\"'.$user[profile_image_url].'\" alt=\"\" title=\"\" /><a href=\"http://twitter.com/'.$user[screen_name].'\" target=\"_blank\">@'.$user[screen_name].'</a>",
            $user['name'],
            $user['description'],
            $badge
            );
          $class=='odd'?$class='even':$class='odd';
        }
      } else {
        $cursor = 0;
      }
    } else {
      $data = json_decode($connection->response['response'], true);
      $dt['data_table'].=$data;
    }
    $dt['json_table']=$json_table;
    echo json_encode($dt);

?>
