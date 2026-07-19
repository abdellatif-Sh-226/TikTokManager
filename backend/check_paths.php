<?php
$router = realpath('vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php');
echo 'Router: ' . $router . PHP_EOL;
echo 'File exists: ' . (file_exists($router) ? 'yes' : 'no') . PHP_EOL;
$public = realpath('public/index.php');
echo 'index.php: ' . $public . PHP_EOL;
echo 'File exists: ' . (file_exists($public) ? 'yes' : 'no') . PHP_EOL;
