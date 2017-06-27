<?php

namespace Zan\Framework\Network\MqSubscribe\Subscribe;

use Zan\Framework\Network\Server\Monitor\Worker;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class Manager
{
    use Singleton;
    
    private $initialized;
    
    /**
     * @var array Topic[topicName]
     */
    private $topics = [];

    private $totalMsgCount = 0;
    
    final private function __construct()
    {
    }

    /**
     * 初始化所有Topic
     * 
     * @param array $config
     * @return $this
     */
    public function init(array $config)
    {
        if ($this->initialized) {
            return $this;
        }

        $this->topics = []; // ? 注意这里是否应该这样
        
        foreach ($config as $topicName => $topicConfig) {
            $this->initTopic($topicName, $topicConfig);
        }

        $this->initialized = true;

        return $this;
    }

    public function start()
    {
        if (!$this->initialized) {
            return;
        }

        foreach ($this->topics as $topic) {
            /** @var Topic $topic*/
            foreach ($topic->getChannels() as $channel) {
                /** @var Channel $channel */
                foreach ($channel->getClients() as $client) {
                    /** @var Client $client */
                    $client->start();

                    if ($client->isError()) {
                        sys_echo($client->getErrorMessage());
                    }
                }
            }
        }
    }

    /**
     * 初始化Topic
     * 
     * @param $name
     * @param array $config
     */
    private function initTopic($name, array $config)
    {
        $topic = new Topic($name, $this);

        foreach ($config as $channelName => $channelConfig) {
            $topic->initChannel($channelName, $channelConfig);
        }

        $this->topics[$name] = $topic;
    }

    /**
     * 通知到所有client, 标记后不再接受msg
     */
    public function closePre()
    {
        foreach ($this->topics as $topic) {
            /** @var Topic $topic*/
            foreach ($topic->getChannels() as $channel) {
                /** @var Channel $channel */
                foreach ($channel->getClients() as $client) {
                    /** @var Client $client */
                    $client->waitingClosed();
                }
            }
        }
    }

    /**
     * 检查所有client是否已经可以关闭
     * 
     * @return bool
     */
    public function checkReadyClose()
    {
        $readyClosed = true;
        foreach ($this->topics as $topic) {
            /** @var Topic $topic*/
            foreach ($topic->getChannels() as $channel) {
                /** @var Channel $channel */
                foreach ($channel->getClients() as $client) {
                    /** @var Client $client */
                    /**
                     * 判断是否被标记了等待关闭 并且 没有msg正在处理中
                     */
                    if (!$client->isWaitingClosed() or !$client->isProcessing()) {
                        $readyClosed = false;
                    }
                }
            }
        }

        return $readyClosed;
    }

    public function incrMsgCount()
    {
        $this->totalMsgCount++;
        Worker::singleton()->incrMsgCount();
    }
}