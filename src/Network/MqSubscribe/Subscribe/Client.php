<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Sdk\Queue\NSQ\Queue;

class Client
{
    const TIMEOUT = 1800;
    
    private $consumer;
    private $timeout;

    /**
     * @var Channel
     */
    private $channel;

    /** @var  Task */
    private $task;
    
    private $sum = 0;
    
    private $error;
    private $errorMessage;

    public function __construct($config, Channel $channel)
    {
        $this->consumer = $config['consumer'];
        $this->timeout = isset($config['timeout']) ? $config['timeout'] : self::TIMEOUT;
        $this->channel = $channel;
    }

    public function getConsumer()
    {
        return $this->consumer;
    }

    public function start()
    {
        if (!$this->valid()) {
            return;
        }
        $this->task = new Task($this->cortinue());
        $this->task->run();
    }
    
    public function isError()
    {
        return $this->error;
    }
    
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function incrMsgCount()
    {
        $this->sum++;
    }

    public function getMsgCount()
    {
        return $this->sum;
    }
    
    private function valid()
    {
        $consumer = $this->getConsumer();
        if (class_exists($consumer) and method_exists($consumer, 'fire')) {
            $this->error = false;
        }
        
        $this->error = true;
        $this->errorMessage = $this->channel->getTopic()->getName() . '/' . $this->channel->getName() . '/Error';
        return !$this->error;
    }

    private function cortinue()
    {
        $queue = new Queue();
        $client = $this;

        yield $queue->subscribe($this->channel->getTopic()->getName(), $this->channel->getName(), function($msg) use ($client){
            $client->incrMsgCount();
            $consumer = $this->getConsumer();
            $instance = Di::make($consumer);
            $instance->setMsg($msg);
            if (!$instance->checkMsg()) {
                $instance->handleMsgError();
                $instance->ack();
                return;
            }
            $handle = $instance->fire();
            Task::execute($handle);
        }, $this->timeout);
    }
}