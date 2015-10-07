<?php
header('Content-type: application/json');
require_once('../../settingan/ketetapan.php');
$screen_name = $_GET['screen_name'];
if (!isset($_SESSION[$screen_name])or isset($_GET['refresh']))
{
  require(LIB_PATH.'oauth_twitter/config.php');
  require(LIB_PATH.'oauth_twitter/oauth_lib.php');
  $connection = get_connection(array('user_token'=>$_SESSION['oauth_token'],'user_secret'=>$_SESSION['oauth_token_secret']));
  $connection->request('GET', $connection->url('1.1/users/show'),
    array(
      'screen_name' => $screen_name
    )
  );
  $user = json_decode($connection->response['response']);
  //~ echo '<pre>'.print_r($user,1).'</pre>';exit();
  $user_status_text = isset($user->status->text)? linkify($user->status->text) : '-';
  $user_status_created_at = isset($user->status->created_at)?twitter_time($user->status->created_at):'-';
  $dt['user']['statuses_count'] = jp_num_format($user->statuses_count);
  $dt['user']['name'] = $user->name;
  $dt['user']['screen_name'] = '@'.$user->screen_name;
  $dt['user']['followers_count'] = jp_num_format($user->followers_count);
  $dt['user']['friends_count'] = jp_num_format($user->friends_count);
  $dt['user']['listed_count'] = jp_num_format($user->listed_count);
  $dt['user']['description'] = linkify($user->description);
  $dt['user']['url'] = $user->url;
  $dt['user']['location'] = $user->location;
  $dt['user_img']['profile_image_url_https'] = $user->profile_image_url_https;
  $dt['user']['status_text'] = $user_status_text;
  $dt['user']['tweet_time'] = $user_status_created_at;
//~ mengambil follower id
//~ dimatikan karena terlalu berat
  //~ $cursor=-1;
  //~ $followers_ids = array();
  //~ do{
    //~ $connection->request('GET', $connection->url('1.1/followers/ids'),
      //~ array(
        //~ 'screen_name' => $screen_name,
        //~ 'cursor' => $cursor,
        //~ 'count' => 5000
      //~ )
    //~ );
    //~ $followers = json_decode($connection->response['response']);
    //~ $followers_ids=array_merge($followers_ids, $followers->ids);
    //~ $cursor=$followers->next_cursor;
  //~ }while($cursor!=0);
  //~ $cursor=-1;
  //~ $friends_ids = array();
  //~ do{
    //~ $connection->request('GET', $connection->url('1.1/friends/ids'),
      //~ array(
        //~ 'screen_name' => $screen_name,
        //~ 'cursor' => $cursor,
        //~ 'count' => 5000
      //~ )
    //~ );
    //~ $friends = json_decode($connection->response['response']);
    //~ $friends_ids=array_merge($friends_ids, $friends->ids);
    //~ $cursor=$friends->next_cursor;
  //~ }while($cursor!=0);
  //~ $dt['mutual']=array_intersect($followers_ids,$friends_ids);
  //~ $dt['balas_budi']=my_array_diff($followers_ids,$dt['mutual']);
  //~ $dt['flush']=my_array_diff($friends_ids,$dt['mutual']);
  //~ $dt['followers_ids']=$followers_ids;
  //~ $dt['friends_ids']=$friends_ids;
  //~ $dt['followers_ids_count']=count($dt['followers_ids']);
  //~ $dt['friends_ids_count']=count($dt['friends_ids']);
  //~ $dt['mutual_count']=count($dt['mutual']);
  //~ $dt['balas_budi_count']=count($dt['balas_budi']);
  //~ $dt['flush_count']=count($dt['flush']);
  //~ $_SESSION['verified']=1;
  echo json_encode($dt);
}
else
{
  echo 0;
}
?>