<?php

require __DIR__ . '/../../../bootstrap.php';


use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Utilities\DesignPattern\Context;

use Zan\Framework\Sdk\Queue\NSQ\Queue;

class NsqClientTest 
{
    public function testPub()
    {
        $context = new Context();

        $coroutine = $this->pub();

        $task = new Task($coroutine, $context, 19);
        $task->run();
    }

    public function testSub()
    {
        $context = new Context();

        $coroutine = $this->sub();

        $task = new Task($coroutine, $context, 19);
        $task->run();
    }

    public function pub()
    {
        $topic = 'fenxiao_goods_index_create';
        $queue = new Queue();

        $msg = \Kdt\Iron\NSQ\Message\Msg::fromClient('wahahahahahah');

        $result = (yield $queue->publish($topic, $msg));
        
        var_dump($result);exit;

    }

    public function sub()
    {
        $topic = 'fenxiao_goods_index_create';
        $channel = 'default';
        $queue = new Queue();

        yield $queue->subscribe($topic, $channel, function($payload){
            var_dump($payload);
            var_dump('dddddddddddd');
        });
    }

    public function tearDown()
    {
        swoole_event_exit();
    }
}

(new NsqClientTest())->testSub();