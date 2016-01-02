<?php

define('APP_PATH', '/Users/hupeipei/www/zan');

require (__DIR__ . '/src/Zan.php');

(new HttpServer)->run($argv[1]);