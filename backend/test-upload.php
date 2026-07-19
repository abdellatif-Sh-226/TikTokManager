<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo 'upload_tmp_dir: ' . ini_get('upload_tmp_dir') . PHP_EOL;
echo 'TMP env: ' . getenv('TMP') . PHP_EOL;
echo 'TEMPDIR: ' . getenv('TEMPDIR') . PHP_EOL;
echo 'TEMP: ' . getenv('TEMP') . PHP_EOL;
echo 'sys_get_temp_dir: ' . sys_get_temp_dir() . PHP_EOL;
echo 'temp writable: ' . (is_writable(sys_get_temp_dir()) ? 'yes' : 'no') . PHP_EOL;

if (!empty($_FILES)) {
    print_r($_FILES);
}
