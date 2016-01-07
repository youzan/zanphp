<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:54
 */

namespace Zan\Framework\Test\Foundation\Core;

use Zan\Framework\Foundation\Core\Config;

require __DIR__ . '/../../../' . 'src/Zan.php';

class ConfigTest extends \UnitTest {
    public function setUp()
    {
        $path = __DIR__ . '/config/';
        Config::setConfigPath($path);
    }

    public function tearDown()
    {
        Config::clear();
    }

    public function testGetConfigWork()
    {
        $data = Config::get('a.b.c');
        $data = Config::get('dir/a.b.c');
    }

}