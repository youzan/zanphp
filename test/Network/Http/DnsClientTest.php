<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/6
 * Time: 下午3:10
 */
namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Network\Common\DnsClient;
use Zan\Framework\Testing\TaskTest;

class AsyncDnsClient implements Async {
    private $callback;

    public function visit($url) {
        DnsClient::lookup($url, function ($host, $ip) {
            call_user_func_array($this->callback, [$host, $ip]);
        }, function () {
            assert(false);
        });
        yield $this;
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = function ($host, $ip) use($callback) {
            $callback([$host, $ip], null);
        };
    }
}

class DnsClientTest extends TaskTest {
    public function taskAccessableHost()
    {
        $client = new AsyncDnsClient();
        yield $client->visit("www.baidu.com");
    }

    public function taskUnAccessableHost()
    {
        $client = new AsyncDnsClient();
        yield $client->visit("unreachable");
    }
}