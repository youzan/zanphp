<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:20
 */

namespace Zan\Framework\Test\Network;

use Zan\Framework\Network\Client\ConnectionConfig;

class ConnectionConfigTest extends \TestCase {
    public function setUp()
    {
        $path = __DIR__ . '/connection/';
        ConnectionConfig::setConfigPath($path);
    }

    public function tearDown()
    {
        ConnectionConfig::clear();
    }

    public function testFileConfigWork()
    {
        $config = ConnectionConfig::get('wsc');

    }
}