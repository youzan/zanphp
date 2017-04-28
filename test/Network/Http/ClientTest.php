<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Common\Client;
use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;
use Zan\Framework\Testing\TaskTest;

class ClientTest extends TaskTest {
    public function taskClientCall()
    {
        try {
            $result = (yield Client::call('fenxiao.supplier.goods.getGoodsByKdtGoodsId', [
                'kdt_goods_id' => 1500107
            ]));
        } catch (\Exception $e) {
            $this->assertInstanceOf(HttpClientTimeoutException::class, $e, "Unexpected exception thrown");
            return;
        }

        yield 'success';
    }
}