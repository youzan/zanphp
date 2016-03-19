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
use Zan\Framework\Testing\TaskTestBase;

class ClientTest extends TaskTestBase {

    public function setUp()
    {
        $path = __DIR__ . '/config/';
        Path::setConfigPath($path);
        RunMode::set('dev');

        Config::init();
        Config::get('http.client');
    }

    public function taskStep()
    {
        $option = [
            'order_no'  => 'E123',
            'kdt_id'    => 1,
        ];
        $result = (yield Client::call('trade.order.detail.byOrderNo', $option));

        $this->assertEquals('{"code":40201,"msg":"\u8ba2\u5355\u4e0d\u5b58\u5728","data":null}', $result, 'fail');
    }
}