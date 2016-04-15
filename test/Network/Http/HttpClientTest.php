<?php
namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Testing\TaskTest;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Foundation\Coroutine\Context;

class HttpClientTest extends TaskTest
{



    public function testTaskCall()
    {
        $context = new Context();
        $task = new Task($this->makeCoroutine($context), null, 8);
        $task->run();

    }

    private function makeCoroutine($context)
    {
        $result = (yield HttpClient::newInstance('api.koudaitong.com', 80)->get('/fenxiao/supplier/goods/getGoodsByKdtGoodsId', [
            'kdt_goods_id' => 1313150,
            'debug' => 'json'
        ]));

        var_dump($result);exit;

        yield 'success';
    }
}