<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

use Zan\Framework\Network\Server\Monitor\Worker;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class Manager
{
    use Singleton;

    const TIME_LIVE_LIMIT = 1000 * 60 * 60; //毫秒, 1个小时
    const TIME_LIVE_LIMIT_DELAY = 2000; //毫秒, 3秒

    private $server;

    private $initialized;
    
    /**
     * @var array Topic[]
     */
    private $topics = [];

    private $totalMsgCount = 0;
    
    final private function __construct()
    {
    }

    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }
    
    public function init($config)
    {
        if ($this->initialized) {

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
            
        }

        foreach ($this->topics as $topic) {
            /** @var Topic $topic*/
            foreach ($topic->getChannels() as $channel) {
                /** @var Channel $channel */
                foreach ($channel->getClients() as $client) {
                    /** @var Client $client */
                    $client->start();

                    if ($client->isError()) {
                        echo $client->getErrorMessage() . "\n";
                    }
                }
            }
        }
    }
    
    private function initTopic($name, $config)
    {
        $topic = new Topic($name, $this);

        foreach ($config as $channelName => $channelConfig) {
            $topic->initChannel($channelName, $channelConfig);
        }

        $this->topics[$name] = $topic;
    }

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

    public function checkReadyClose()
    {
        $readyClosed = true;
        foreach ($this->topics as $topic) {
            /** @var Topic $topic*/
            foreach ($topic->getChannels() as $channel) {
                /** @var Channel $channel */
                foreach ($channel->getClients() as $client) {
                    /** @var Client $client */
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