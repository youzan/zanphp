<?php

define('APP_PATH', dirname(__DIR__));

return [
    'config_path'       =>   APP_PATH . '/resource/config',
    'cache_path'        =>   APP_PATH . '/resource/cache',
    'sql_path'          =>   APP_PATH . '/resource/sql',
    'pre_filter_path'   =>   APP_PATH . '/init/PreFilter',
    'post_filter_path'  =>   APP_PATH . '/init/PostFilter',
    'routing_path'      =>   APP_PATH . '/init/routing',
];