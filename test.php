<?php

define('APP_PATH', '/Users/hupeipei/www/zan');

require (__DIR__ . '/src/Zan.php');

\Zan\Framework\Zan::createHttpApplication([])->run();