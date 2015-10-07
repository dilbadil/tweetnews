<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');
$args = array(
  'user_dirs' => true,
  'upload_dir' => '/opt/lampp/htdocs/tweepi/upload/files/',
  'upload_url' => 'http://projecto.com/tweepi/upload/files'
);
$upload_handler = new UploadHandler($args);
