<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/6
 * Time: 下午3:10
 */

use Zan\Framework\Test\Foundation\Coroutine\TaskTest;
use Zan\Framework\Network\Common\DnsClient;

class DnsClientTest extends TaskTest {
    public function testAccessableHost()
    {
        DnsClient::lookup("www.baidu.com", function ($host, $ip) {
            var_dump(func_get_args());
        });
    }
    public function testUnAccessableHost()
    {
        DnsClient::lookup("unreachable", function ($host, $ip) {
            var_dump(func_get_args());
        },
        function () {
            var_dump(func_get_args());
        });
    }
}