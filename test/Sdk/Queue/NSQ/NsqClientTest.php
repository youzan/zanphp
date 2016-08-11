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
        $topicA = 'fenxiao_goods_index_create';
        $topicB = 'fenxiao_goods_index_update';
        $queue = new Queue();
        
        $dataA = [
            'job_name' => 'fenxiao_goods_index_create',
            'job_key' => 'fenxiao_goods_index_create_bar_p',
            'job_data' => [
                'test' => 'bar_p'
            ],
            'job_time' => date('Y-m-d H:i:s'),
        ];

        $dataB = [
            'job_name' => 'fenxiao_goods_index_update',
            'job_key' => 'fenxiao_goods_index_update_foo_p',
            'job_data' => [
                'test' => 'foo_p'
            ],
            'job_time' => date('Y-m-d H:i:s'),
        ];

        $msgA = \Kdt\Iron\NSQ\Message\Msg::fromClient($dataA);
        $msgB = \Kdt\Iron\NSQ\Message\Msg::fromClient($dataB);

        $resultA = (yield $queue->publish($topicA, $msgA));
        $resultB = (yield $queue->publish($topicB, $msgB));

        var_dump($resultA, $resultB);exit;

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

(new NsqClientTest())->testPub();