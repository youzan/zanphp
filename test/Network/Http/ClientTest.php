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

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Foundation\Coroutine\Context;

class ClientTest extends TaskTest {

    public function setUp()
    {
        $path = __DIR__ . '/config/';
        Path::setConfigPath($path);
        RunMode::set('dev');

        Config::init();
        Config::get('services.php');
    }

    public function testTaskCall()
    {
        $context = new Context();
        $task = new Task($this->makeCoroutine($context), null, 8);
        $task->run();

    }

    private function makeCoroutine($context)
    {
         $result = (yield Client::call('fenxiao.supplier.goods.getGoodsByKdtGoodsId', [
            'kdt_goods_id' => 1500107
        ]));
        $context->set('result', $result);

        var_dump($result);exit;

        yield 'success';
    }
}