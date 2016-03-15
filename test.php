<?php

define('APP_PATH', __DIR__);

require (__DIR__ . '/src/Zan.php');

\Zan\Framework\Zan::createHttpApplication([])->run();