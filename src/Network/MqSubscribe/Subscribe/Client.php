<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

use Kdt\Iron\NSQ\Message\Msg;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Sdk\Queue\NSQ\Queue;

class Client
{
    /**
     * @var string
     */
    private $consumer;

    /**
     * @var Channel
     */
    private $channel;

    /**
     * @var  Task
     */
    private $task;
    
    private $totalMsgCount = 0;

    private $isWaitingClosed = false;
    private $isProcessing = false;
    private $isWorking = false;
    
    private $error;
    private $errorMessage;

    public function __construct($config, Channel $channel)
    {
        $this->consumer = $config['consumer'];
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
        if ($this->isWaitingClosed()) {
            return;
        }

        $this->init();
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
        $this->totalMsgCount++;
        $this->channel->incrMsgCount();
    }

    public function getMsgCount()
    {
        return $this->totalMsgCount;
    }

    public function waitingClosed()
    {
        $this->isWaitingClosed = true;
    }

    public function isWaitingClosed()
    {
        return $this->isWaitingClosed;
    }

    public function processing()
    {
        $this->isProcessing = true;
    }

    public function free()
    {
        $this->isProcessing = false;
    }
    
    public function isProcessing()
    {
        return $this->isProcessing;
    }
    
    private function valid()
    {
        $consumer = $this->getConsumer();
        if (class_exists($consumer) and method_exists($consumer, 'fire')) {
            $this->error = false;
        } else {
            $this->error = true;
            $this->errorMessage = $this->channel->getTopic()->getName() . '/' . $this->channel->getName() . '/Error';
        }

        return !$this->error;
    }

    private function init()
    {
        $this->totalMsgCount = 0;
        $this->isWaitingClosed = false;
        $this->isProcessing = false;
        $this->isWorking = false;
    }

    private function cortinue()
    {
        $queue = new Queue();
        $client = $this;
        $this->isWorking = true;

        /**
         * 为了避免cortinue内存不释放,造成递归调用,内存溢出, 这里在sub回调里判断count是否超过limit重开client
         *
         * sub回调里面的调用是不会导致内存溢出的
         */
        yield $queue->subscribe($this->channel->getTopic()->getName(), $this->channel->getName(), function($msg) use ($client){
            /** @var $msg Msg */
            /**
             * 检查是否被通知到不要再接受msg了
             * 直接return后 会在超时内不会再收到server发送的msg,
             * 消息没有ack, 会重新发送, 就算不是REQ server发送的msg数据包内的计数也会增加
             */
            if ($client->isWaitingClosed()) {
                return;
            }

            $client->processing();

            $client->incrMsgCount();
            $consumer = $this->getConsumer();
            $instance = Di::make($consumer);
            $instance->setMsg($msg);
            if (!$instance->checkMsg()) {
                $instance->handleMsgError();
                $msg->done();
                return;
            }
            $handle = $instance->fire();
            Task::execute($handle);

            $client->free();
        }, 0); //0表示 不需要lib强制断开连接
    }
}