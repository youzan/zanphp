<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Common\Client;
use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Foundation\Coroutine\Context;

class ClientTest extends \TestCase {
    public function testTaskCall()
    {
        $context = new Context();
        $task = new Task($this->makeCoroutine($context), null, 8);
        $task->run();
    }

    private function makeCoroutine($context)
    {
        try {
            $result = (yield Client::call('fenxiao.supplier.goods.getGoodsByKdtGoodsId', [
                'kdt_goods_id' => 1500107
            ]));
        } catch (\Exception $e) {
            $this->assertInstanceOf(HttpClientTimeoutException::class, $e, "Unexpected exception thrown");
            return;
        }

        $context->set('result', $result);
        yield 'success';
    }
}