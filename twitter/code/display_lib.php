<?php
function display_template($nav, $merge = array()) {
  #initialize twig
  $lang = display_lang();
  $tmpl = display_file($nav);

  require_once SEKRIP_PATH.'Twig/lib/Twig/Autoloader.php';
  Twig_Autoloader::register();
  $loader = new Twig_Loader_Filesystem(TMPL_PATH);
  $twig = new Twig_Environment($loader, array(
      //~ 'cache' => TMPL_PATH.'cache',
  ));
  $template = $twig->loadTemplate($tmpl.'.phtml');

  #initialize modul var
	//if($_SESSION['sisa_hari']=='-1'){
	if($_SESSION['sisa_hari']<0){
		$sisa_hari = '---';
	} else {
		$sisa_hari = $_SESSION['sisa_hari'];
	}
  $source = json_decode(file_get_contents(TMPL_PATH.$lang.'.json'), true)[$nav];
  //~ echo '<pre>'.print_r($source,1).'</pre>';exit();
  $params = array(
    'sisa_hari' => $sisa_hari
  );
  $source = array_merge($source, $params);
  $source = array_merge($source, $merge);

  return $template->render($source);
}

function display_lang() {
  $lang = 'sources';
  if (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
  }
  return $lang;
}

function display_file($nav) {
  switch ($nav) {
    case 'follow_list';
      $tmpl = 'list_table';
      break;
    case 'tweet_search';
      $tmpl = 'tweet_table';
      break;
    case 'penjadwalan_tweet';
      $tmpl = 'penjadwalan_form';
      break;
    case 'kultweet';
      $tmpl = 'kultweet';
      break;
    default :
      $tmpl = 'jp_table';
    break;
  }
  return $tmpl;
}

function linkify($text) {
  $text = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $text);
  $text = preg_replace("/\B@(\w+(?!\/))\b/i", '<a href="https://twitter.com/\\1" target=\"_blank\">@\\1</a>', $text);
  $text = preg_replace("/\B(?<![=\/])#([\w]+[a-z]+([0-9]+)?)/i",'<a href="https://twitter.com/#!/search/%23\\1" target=\"_blank\">#\\1</a>', $text);
  return $text;
}

function twitter_time($time) {
  $delta = time() - strtotime($time);
  if ($delta < 60) {
    return 'less than a minute ago';
  } else if ($delta < 120) {
    return 'about a minute ago';
  } else if ($delta < (60 * 60)) {
    return floor($delta / 60) . ' minutes ago';
  } else if ($delta < (120 * 60)) {
    return 'about an hour ago';
  } else if ($delta < (24 * 60 * 60)) {
    return floor($delta / 3600) . ' hours ago';
  } else if ($delta < (48 * 60 * 60)) {
    return '1 day ago';
  } else {
    return number_format(floor($delta / 86400)) . ' days ago';
  }
}
?>
