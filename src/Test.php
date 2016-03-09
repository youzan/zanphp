<?php
/**
 * Test Script init file
 * User: winglechen
 * Date: 15/10/22
 * Time: 15:26
 */
namespace Zan\Framework;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Zan.php';

define('SQL_PATH', dirname(__DIR__) . '/test/Store/Database/Sql/resources/');
class Test {
    public static function init()
    {

    }
}
Zan::init();
Test::init();
