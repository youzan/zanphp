<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/8
 * Time: 23:04
 */

require __DIR__ . '/Middleware.php';

Middleware::group([
    'web'    => [

    ],
    'auth'  => [

    ],
]);

Middleware::match('/*.php/i','');