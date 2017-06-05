<?php


/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/
use Zan\Framework\Foundation\Application;

require __DIR__ . '/../vendor/autoload.php';

$appName = 'zan-test';
$rootPath = realpath(__DIR__);

$app = new Application($appName, $rootPath);

return $app;

