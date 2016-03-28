<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Network\Common\Client;
use Zan\Framework\Testing\TaskTest;

class ClientTest extends TaskTest {

    public function setUp()
    {
        $path = __DIR__ . '/config/';
        Path::setConfigPath($path);
        RunMode::set('dev');

        Config::init();
        Config::get('http.client');
    }

    public function taskCall()
    {
        $option = [
            'order_no'  => 'E123',
            'kdt_id'    => 1,
        ];
        $result = (yield Client::call('trade.order.detail.byOrderNo', $option));

        $this->assertEquals(3, count($result), 'fail');
    }
}