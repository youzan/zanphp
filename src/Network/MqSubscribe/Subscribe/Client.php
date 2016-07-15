<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Sdk\Queue\NSQ\Queue;

class Client
{
    private $consumer;

    /**
     * @var Channel
     */
    private $channel;

    /** @var  Task */
    private $task;
    
    private $sum;

    public function __construct($consumer, Channel $channel)
    {
        $this->consumer = $consumer;
        $this->channel = $channel;
    }

    public function start()
    {
        $this->task = new Task($this->cortinue());
        $this->task->run();
    }

    private function cortinue()
    {
        $queue = new Queue();
        $client = $this;

        yield $queue->subscribe($this->channel->getTopic()->getName(), $this->channel->getName(), function($payload) use ($client){
            var_dump('vvvvv');
        });
    }
}