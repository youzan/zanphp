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

    /**
     * @var bool
     */
    private $error;
    /**
     * @var string
     */
    private $errorMessage;

    public function __construct($config, Channel $channel)
    {
        $this->consumer = $config['consumer'];
        $this->channel = $channel;
    }

    /**
     * 获取执行消息的类名
     *
     * @return string
     */
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

    /**
     * 获取是否启动错误
     *
     * @return bool
     */
    public function isError()
    {
        return $this->error;
    }

    /**
     * 获取启动错误信息
     *
     * @return string
     */
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

    /**
     * 标记Client为等待关闭状态
     * 被标记后Client不再接受新的Msg
     * 新接受到的Msg不再处理, 不FIN 也不REQ
     */
    public function waitingClosed()
    {
        $this->isWaitingClosed = true;
    }

    /**
     * 判断当前是否已经被标记为等待关闭状态
     *
     * @return bool
     */
    public function isWaitingClosed()
    {
        return $this->isWaitingClosed;
    }

    /**
     * 设置当前为正在处理Msg状态
     */
    public function processing()
    {
        $this->isProcessing = true;
    }

    /**
     * 设置当前为不在处理Msg状态
     */
    public function free()
    {
        $this->isProcessing = false;
    }

    /**
     * 判断当前是否正在处理Msg
     *
     * @return bool
     */
    public function isProcessing()
    {
        return $this->isProcessing;
    }

    /**
     * 检查consumer 是否可以执行
     * @return bool
     */
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