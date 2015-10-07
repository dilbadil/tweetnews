<?php
require_once('../../settingan/ketetapan.php');
error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');
$args = array(
  'user_dirs' => true,
  'upload_dir' => UPLOAD_PATH,
  'upload_url' => UPLOAD_URL
);
$upload_handler = new UploadHandler($args);
