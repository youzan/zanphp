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
        $params = [
            'txt' => 'aaa',
            'size' => 200,
            'margin' => 20,
            'level' => 0,
            'hint' => 2,
            'case' => 1,
            'ver' => 1,
            'fg_color' => '000000',
            'bg_color' => 'ffffff',
        ];
        $result = (yield HttpClient::newInstance('192.168.66.202', 8888)->get('', $params));

        var_dump($result);
        exit;

        yield 'success';
    }
}