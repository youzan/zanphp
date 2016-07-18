<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

use Zan\Framework\Foundation\Container\Di;
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

    public function getConsumer()
    {
        return $this->consumer;
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

        yield $queue->subscribe($this->channel->getTopic()->getName(), $this->channel->getName(), function($msg) use ($client){
            $consumer = $client->getConsumer();
            $instance = Di::make($consumer);
            $instance->setMsg($msg);
            $instance->checkMsg();
            $handle = $instance->fire();
            Task::execute($handle);
        });
    }
}