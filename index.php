<?php
session_start();
require_once "config.php";
require_once('lib/ganon.php');

// if (isset($_SESSION['user_id'])) {
// 	if ($_SESSION['user_id'] != '1889885564') {
// 		die('Akses ditolak');
// 	}
// }

function update_file_tweeted () {
	//$file_tweeted = 'data/tweeted.txt';
	//$tweeted_lama = file_get_contents($file_tweeted);
	//$arr_tweeted = json_decode($tweeted_lama, 1);
	//$arr_news = $_SESSION['news_tribun_new'];
	//$a = array_diff($arr_tweeted, $arr_news);
	//$b = array_diff($arr_tweeted, $a);
	//$c = json_encode($b);
	//file_put_contents($file_tweeted, $c);
}

function update_sess_notif_tb ($newer, $older) {
	$d = array_diff($newer, $older);
	if (! empty($d)) {
		foreach ($d as $vd) {
			$_SESSION['news_tribun_notif'][] = $vd;
		}
	}	
}

if (isset($_GET['test'])) {
	echo "<pre>".print_r($_SESSION,1)."</pre>";
	//$file_tweeted = 'data/tweeted.txt';
	//$tweeted_lama = file_get_contents($file_tweeted);
	//$arr_tweeted = json_decode($tweeted_lama, 1);
	//echo "twteed<pre>".print_r($arr_tweeted,1)."</pre>";
	//echo "notif<pre>".print_r($_SESSION['news_tribun_notif'],1)."</pre>";
	//echo "jum notif<pre>".print_r($_SESSION['jum_notif'],1)."</pre>";
	//echo "new1<pre>".print_r($new,1)."</pre>";
	//echo "new_ori<pre>".print_r($_SESSION['news_tribun_new'],1)."</pre>";
	die();
}

if (empty($_SESSION['logged'])) {
    $loginUrl = BASE_URL . "twitter/login.php";
    $res = "
        <h1>Welcome to Tweetnews</h1>
        <p>This is an application to read news</p>
        <p>Please sign in to use this application.</p>
        <button style='padding: 5px;cursor: pointer;'>
            <a href='".$loginUrl."'>
                <img src='https://g.twimg.com/dev/sites/default/files/images_documentation/sign-in-with-twitter-link.png' alt='Sign in with Twitter'>
            </a>
        </button>
        ";
    die($res);
	// $_SESSION['user_id'] = '1889885564';
	// $_SESSION['screen_name'] = 'jogjaBox';
	// $_SESSION['oauth_token_secret'] = 'tvdksk2moWlrN7LPEIftzCk3Mki7BCssDEJwxqjcKPcVF';
	// $_SESSION['oauth_token'] = '1889885564-v53R10a9ZaPDTRE250ZmAELksai2TMiWMQHihPH';
	// $_SESSION['oauth_verifier'] = 'HNIHEhcTWeDSgB3Cy6udN0X1rudXUm8nVqaXXric';
}

$res = "
	<title>Jogja News</title>
	<link rel='stylesheet' href='css/jquery.ui.all.css'>
	<link rel='stylesheet' href='css/demos.css'>
	<link rel='stylesheet' href='css/my.css'>
	<link rel='stylesheet' href='lib/jquery.qtip.custom/jquery.qtip.min.css'>
	<script src='js/jquery-2.0.3.min.js'></script>
	<script src='js/jquery.ui.core.js'></script>
	<script src='js/jquery.ui.widget.js'></script>
	<script src='js/jquery.ui.accordion.js'></script>
	<script src='lib/jquery.qtip.custom/jquery.qtip.min.js'></script>
	<script src='js/blockUi.js'></script>

	<div style='text-align:center;'>
		<button class='btn-scaper' id='tribun_jogja_reload'>Tribun Jogja</button>
		<button class='btn-scaper' id='kr_reload'>KR</button>
		";
		if (! isset($_SESSION['user_id'])) {
			$res .=	"<a style='text-decoration: initial;' href='twitter/login.php?nav=via_twitter'>Sign in</a>";
		} else {
			$res .=	"Logged as <a style='text-decoration: initial;' href='javascript:;'>@$_SESSION[screen_name]</a> | <a style='text-decoration: initial;' href='twitter/login.php?nav=via_twitter&force_login=1'>Change</a> | <a style='text-decoration: initial;' href='".BASE_URL."twitter/login.php?nav=logout'>Logout</a>";
		}
	$res .= "
	</div>
	<script>
	$(document).ready(function () {
		$('#tribun_jogja_reload').click(function(){
			$.ajax({
				type: 'get',
				url: '',
				data: 'scrape=tribun',
				dataType: 'json',
				beforeSend: function() {
					//$('#tribun_jogja').html('<img style=\"margin: 0 45%;\" src=\"img/loading_bulet.gif\" />');
					$('#tribun_jogja_reload').prop('disabled', true);
				},
				success: function(json) {
					$('#ui-accordion-accordion-panel-0').html(json.html);					
					$('#tribun_jogja_reload').prop('disabled', false);
					if (json.jum_notif > 0) {
						$('.ket_tribun_notif').html(' (' + json.jum_notif + ')');
						var titel = $('title').text();
						//if  (titel === '".TITLE."') {
							$('title').html('(' + json.jum_notif + ') ' + '".TITLE."');
						//}
					}
				}
			});			
		}).click();
		$('#kr_reload').click(function(){
			$.ajax({
				type: 'get',
				url: '',
				data: 'scrape=kr',
				dataType: 'html',
				beforeSend: function() {
					//$('#tribun_jogja').html('<img style=\"margin: 0 45%;\" src=\"img/loading_bulet.gif\" />');
					$('#kr_reload').prop('disabled', true);
				},
				success: function(res) {
					$('#kr').html(res);					
					$('#kr_reload').prop('disabled', false);					
				}
			});			
		});
		
		$('#accordion').accordion({
			collapsible: true,
			heightStyle: 'content'
		});
				
		setInterval(function () {
			$('#tribun_jogja_reload').click();
		}, 300000);
	
	});
	</script>
	<br>
	<div id='accordion'>
		<h3>Tribun<span class='ket_tribun_notif'></span></h3>
		<div>
			<img style=\"margin: 0 44%;\" src=\"img/loading_bulet.gif\" />
		</div>	
		<h3>KR</h3>
		<div id='kr_div'>
		</div>
	</div>
	<div style='' id='tribun_jogja'></div>
	<div style='' id='kr'></div>
	";
if (empty($_GET)) {
	die($res);
} else if (isset($_GET['read'])) {
	$url = $_GET['url'];
	$html = file_get_dom($url);
	$news = $html('.txt-article');
	foreach ($news as $v) {
		echo $v->html();
	}
	die();
} else if (isset($_GET['manage_tweeted'])) {
	$url = $_GET['url'];
	$file_tweeted = 'data/tweeted.txt';
	$tweeted_lama = file_get_contents($file_tweeted);
	$arr_tweeted = json_decode($tweeted_lama, 1);
	$arr_tweeted[] = $url;
	file_put_contents($file_tweeted, json_encode($arr_tweeted));
} else if (isset($_GET['delete_notif'])) {
	if ($_GET['news'] == 'tribun') {
		$_SESSION['jum_notif'] = 0;
		$_SESSION['news_tribun_old'] = $_SESSION['news_tribun_new'];
		unset($_SESSION['news_tribun_notif']);
	}
} else {
	$file_tweeted = 'data/tweeted.txt';
	$tweeted_lama = file_get_contents($file_tweeted);
	$arr_tweeted = json_decode($tweeted_lama, 1);
	if ($_GET['scrape'] == 'tribun') {	//scrape for tribun jogja
		if (isset($_SESSION['news_tribun_new'])) {
			$arr_tribun_new_ori = $_SESSION['news_tribun_new'];
			unset($_SESSION['news_tribun_new']);
		}
		$html = file_get_dom('http://jogja.tribunnews.com/');
		
		$news = $html('#latestul');
		$res = "";
		foreach ($news as $v) {
			$childCount = $v->childCount();
			for ($i = 0; $i < $childCount; $i++) {
				$theChild = $v->getChild($i);
				$juduls = $theChild('.f16 a');
				foreach ($juduls as $isi_v) {
					$href = $isi_v->href;
					$text = $isi_v->getPlainText();
					$arr_tribun_new[] = $href;
					//$_SESSION['news_tribun_old'][] = $href;
					if (! isset($_SESSION['news_tribun_old'])) {
						$_SESSION['news_tribun_old'][] = $href;
					}
					$_SESSION['news_tribun_new'] = $arr_tribun_new;
					
					if (isset($arr_tribun_new_ori)) {
						update_sess_notif_tb($arr_tribun_new, $arr_tribun_new_ori);
					}
					
					$class_notif = "";
					if (isset($_SESSION['news_tribun_notif'])) {
						foreach ($_SESSION['news_tribun_notif'] as $v_notif) {
							if ($href == $v_notif) {
								$class_notif = "hidden-news";
								break;
							} else {
								$class_notif = "";
							}
						}
					}

					$class_tweeted = '';
					$res .= "
					<div class='news $class_notif' id='news-tb-$i' style=''>
						<div style='width:415px;float:left;' class='template'>$text <br><a href='$href' target='_blank'>$href</a></div>
						<div style='float:right;'>
						";
						//if (isset($_SESSION['user_id'])) {
						if (! empty($arr_tweeted)) {
							foreach ($arr_tweeted as $url_tweeted) {
								if ($href == $url_tweeted) {
									$class_tweeted = "class='tweeted'";
									break;
								} else {
									$class_tweeted = '';
								}
							}
						}	
							$res .= "
							<button dt-href='$href' $class_tweeted class='btn-tweet'  style='' onclick='tweet(this)' id='button-tb-$i'>Tweet</button><br>
							<button dt-href='$href' has-read='0' onclick='read(this)' class='btn-read' style='' id='button-read-tb-$i'>Read</button>
							";
						//}
					$res .= "
						</div>
						<div style='clear:both;'></div>
					</div>";
				}
			}
		}
		
		$new_arr_news = array_diff($_SESSION['news_tribun_new'], $_SESSION['news_tribun_old']);
		$_SESSION['jum_notif'] = ! empty($new_arr_news) ? count($new_arr_news) : 0;
		$_SESSION['news_tribun_notif'] = $new_arr_news;
		
		if (isset($_SESSION['user_id'])) {
			$res .= "
			<script>
			function tweet (that) {
				var template = $(that).parent().parent().find('.template').text();
				console.log(template);
				var tweetLength = template.length;
				if (tweetLength > 140) {
					//alert('Maksimal 140 karakter');
					//return false;
				}

				$.ajax({
					url: 'twitter/code/tweet_by_screenname.php',
					cache: true,
					beforeSend: function ( xhr ) {
						 $(that).prop('disabled', true);
					},
					type: 'get',
					dataType: 'json',
					data: 'status=' + template,
					success: function(resp) {
						$(that).prop('disabled', false);				
						if (resp.status == 'ok') {
							//$(that).addClass('tweeted');
							$(that).prop('class', 'tweeted');
							$.ajax({
								url: '',
								cache: true,
								beforeSend: function ( xhr ) {
								},
								type: 'get',
								dataType: 'json',
								data: 'manage_tweeted=1&url=' + $(that).attr('dt-href'),
								success: function(resp) {
									
								}
							});
						} else {
							console.log('twitt gagal');
						}
					}
				});
			}
			
			function read (that) {
				$('.qtip-close').click();
				if ($(that).attr('has-read') == '0') {
					$(that).qtip({
						content: {
							text: '<img src=\"img/busy.gif\" /> Reading...',
							title: 'Read',
							button: 'close',
							ajax: {
								url: '',
								type: 'GET',
								beforeSend: function( xhr ) {
									$(that).prop('disabled', true);
								},
								data: 'read=1&url=' + $(that).attr('dt-href'),
								dataType: 'html',
								success: function(data, status) {
									this.set('content.text', data);
									$(that).attr('has-read', '1');
									$(that).prop('disabled', false);
								}
							}
						},
						position: {
							my: 'top center',
							at: 'top center',
							target: $(that).parent().parent()
						},
						show: {
							effect: function(offset) {
								$(this).fadeIn('slow');
							},
							event: 'click',
							ready: true
						},
						hide: 'click',						
						style: {
							classes: 'qtip-wiki qtip-green qtip-shadow',
							width: '700px'
						}
					});								
				}
			}
			
			function show_hidden_news_tb (that) {
				$('.hidden-news').fadeIn('1000');
				$('.hidden-news').removeClass('hidden-news');
				$(that).remove();
				$('.ket_tribun_notif').html('');
				$('title').html('".TITLE."');
				$.ajax({
					type: 'get',
					url: '',
					data: 'delete_notif=1&news=tribun',
					dataType: 'json',
					beforeSend: function() {
					},
					success: function(res) {				
					}
				});				
			}						
			
			</script>
			";
		}
		update_file_tweeted();
		if ($_SESSION['jum_notif'] > 0) {
			$ket_notif = "<div onclick='show_hidden_news_tb(this)' style='' id='id-ket-tribun-notif'>$_SESSION[jum_notif] New News</div>";
		} else {
			$ket_notif = '';
		}
		$results['html'] = $ket_notif . $res;
		$results['jum_notif'] = $_SESSION['jum_notif'];
		die(json_encode($results));
	} else if ($_GET['scrape'] == 'kr') {
		$html = file_get_dom('http://krjogja.com/');

		$news = $html('#tabs-1');
		echo "<pre>".print_r($news,1)."</pre>";
		$res = "";
		foreach ($news as $v) {
			$childCount = $v->childCount();
			for ($i = 0; $i < $childCount; $i++) {
				$theChild = $v->getChild($i);
				$juduls = $theChild('li a');
				foreach ($juduls as $isi_v) {
					$href = $isi_v->href;
					$text = $isi_v->getPlainText();
					$res .= "
					<div class='news' id='news-kr-$i' style='margin-bottom:10px;height:70px;padding:4px;border:1px solid black;'>
						<div style='width:415px;float:left;' class='template'>$text <br><a href='$href' target='_blank'>$href</a></div>
						<div style='float:right;'>
						";
						if (isset($_SESSION['user_id'])) {
							$res .= "<button style='cursor:pointer;padding:12px;' onclick='tweet(this)' id='button-kr-$i'>Tweet</button>";
						}
					$res .= "
						</div>
					</div>";
				}
			}
		}
		die($res);
	}
}
