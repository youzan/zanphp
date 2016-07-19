<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Sdk\Queue\NSQ\Queue;

class Client
{
    const TIMEOUT = 1800;

    const LIMIT_MSG_COUNT = 1000; //待议
    
    private $consumer;
    private $timeout;

    /**
     * @var Channel
     */
    private $channel;

    /** @var  Task */
    private $task;
    
    private $count = 0;
    
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
        $this->count++;
    }

    public function getMsgCount()
    {
        return $this->count;
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

        /**
         * 为了避免cortinue内存不释放,造成递归调用,内存溢出, 这里在sub回调里判断count是否超过limit重开client
         * 改由定时器触发检查
         * sub回调里面的调用是不会导致内存溢出的
         */
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
            /**
             * job里的ack等改为标记
             * 真正的ack在Task外面 也就是下面做 根据是否count超过limit了选择 rdy还是仅仅Fin后重启client
             */
            Task::execute($handle);
        }, $this->timeout);
    }
}