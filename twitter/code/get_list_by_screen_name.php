<?php
require_once('../../settingan/ketetapan.php');
    $screen_name=$_POST['screen_name'];
    require(LIB_PATH.'oauth_twitter/config.php');
    require(LIB_PATH.'oauth_twitter/oauth_lib.php');
    $connection = get_connection(array('user_token'=>$_SESSION['oauth_token'],'user_secret'=>$_SESSION['oauth_token_secret']));
    $connection->request('GET', $connection->url('1.1/lists/ownerships'),
      array('screen_name' => $screen_name,
      'cursor' => -1,
      'count' => 200
    ));
    $http_code = $connection->response['code'];
    if ($http_code == 200) {
      $data = json_decode($connection->response['response'], true);
      $lists = $data['lists'];
      $cursor = $data['next_cursor_str'];
      $dt['data_table']='';
      $json_table=array();
      if (sizeof($lists)) {
        foreach($lists as $list) {
          $badge ='';
          $badge.='<button type="button" class="list">Show User</button> ';
          if ($list['mode']!="public")
          {
            $badge.='<span>private </span> ';
          }
          else
          {
            $badge.='<span>public </span> ';
          }
          if (isset($list['uri']))
          {
            $badge.='<a href="http://twitter.com'.$list['uri'].'">visit list</a> ';
          }
          $json_table[]=array(
            $list['name'],
            $badge,
            $list['member_count'],
            $list['subscriber_count'],
            $list['description']
          );
        }
      } else {
        $cursor = 0;
      }
    } else {
      $data = json_decode($connection->response['response'], true);
    }
    $dt['screen_name']='Showing '.count($json_table).' of <span class="jp_listed_count">-</span> @<span id="list_owner">'.$screen_name.'</span>\'s list.';
    $dt['json_table']=$json_table;
    echo json_encode($dt);

?>