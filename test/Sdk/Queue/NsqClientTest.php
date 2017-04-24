<?php
namespace Zan\Framework\Test\Sdk\Queue;

use Zan\Framework\Testing\TaskTest;
use Kdt\Iron\NSQ\Message\Msg;
use Zan\Framework\Sdk\Queue\NSQ\Queue;

class NsqClientTest extends TaskTest
{
    private $data;

    public function taskPubSub()
    {
        yield $this->sub();
        yield $this->pub();
    }

    private function pub()
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

        $this->data = $dataA;
        $dataB = [
            'job_name' => 'fenxiao_goods_index_update',
            'job_key' => 'fenxiao_goods_index_update_foo_p',
            'job_data' => [
                'test' => 'foo_p'
            ],
            'job_time' => date('Y-m-d H:i:s'),
        ];

        $msgA = Msg::fromClient($dataA);
        $msgB = Msg::fromClient($dataB);

        $resultA = (yield $queue->publish($topicA, $msgA));
        $resultB = (yield $queue->publish($topicB, $msgB));
        yield $queue->publish($topicB, $msgB);
        yield $queue->publish($topicB, $msgB);

        $this->assertEquals($resultA["result"], "ok");
        $this->assertEquals($resultB["result"], "ok");
        yield $resultA;
        yield $resultB;
    }

    private function sub()
    {
        $topic = 'fenxiao_goods_index_create';
        $channel = 'default';
        $queue = new Queue();

        yield $queue->subscribe($topic, $channel, function($msg) {
            $this->assertEquals($msg->data(), $this->data);
        });
    }
}