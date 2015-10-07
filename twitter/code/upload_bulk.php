<?php
require_once('../../settingan/ketetapan.php');
require_once('tw_lib.php');

error_reporting(E_ALL | E_STRICT);
$args = array(
  'user_dirs' => true,
  'upload_dir' => CSV_PATH,
  'upload_url' => CSV_URL
);
require('UploadHandler.php');

class CustomUploadHandler extends UploadHandler {

    protected function handle_form_data($file, $index) {
        $csv = $_FILES['files']['tmp_name'][0];
        $csvFile = fopen($csv, 'r');
        $arr = array();
        while (($line = fgetcsv($csvFile)) !== FALSE) {
          $arr[] = $line;
        }
        fclose($csvFile);
        $file->ids = tw_add_jadwal_arr($arr);
        $file->table = tw_get_jadwal_by_id_arr($file->ids);
    }
}

$upload_handler = new CustomUploadHandler($args);