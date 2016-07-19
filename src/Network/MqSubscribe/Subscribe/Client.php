<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

use Kdt\Iron\NSQ\Message\Msg;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Sdk\Queue\NSQ\Queue;

class Client
{
    const TIMEOUT = 1800; //秒

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

    private $isWaitingClosed = false;
    private $isProcessing = false;
    
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
        $this->count++;
    }

    public function getMsgCount()
    {
        return $this->count;
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
        $this->count = 0;
        $this->isWaitingClosed = false;
        $this->isProcessing = false;
    }

    private function cortinue()
    {
        $queue = new Queue();
        $client = $this;

        $this->init();

        /**
         * 为了避免cortinue内存不释放,造成递归调用,内存溢出, 这里在sub回调里判断count是否超过limit重开client
         *
         * sub回调里面的调用是不会导致内存溢出的
         */
        yield $queue->subscribe($this->channel->getTopic()->getName(), $this->channel->getName(), function($msg) use ($client){
            /** @var $msg Msg */
            /**
             * 检查是否超过msg接受数量限制或者等待关闭中, 超过就关闭连接, 连接关闭后会跳出callback
             */
            if ($client->getMsgCount() >= Client::LIMIT_MSG_COUNT or $client->isWaitingClosed()) {
                $msg->close();
                Timer::after(2000, [$client, 'start']);
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
        }, $this->timeout);
    }
}