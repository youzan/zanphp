<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/3
 * Time: 下午6:23
 */

use Zan\Framework\Network\Common\DnsClient;

class DnsClientTest extends PHPUnit_Framework_TestCase {
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