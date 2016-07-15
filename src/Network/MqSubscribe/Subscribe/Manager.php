<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;


use Zan\Framework\Utilities\DesignPattern\Singleton;

class Manager
{
    use Singleton;

    private $initialized;

    private $config;

    /**
     * @var array Topic[]
     */
    private $topics = [];
    
    final private function __construct()
    {
    }

    public function loadConfig($config)
    {
        $this->config = $config;
    }
    
    public function init()
    {
        if ($this->initialized) {

        }

        $this->topics = []; // ? 注意这里是否应该这样
        
        foreach ($this->config as $topicName => $topicConfig) {
            $this->initTopic($topicName, $topicConfig);
        }

        $this->initialized = true;

        return $this;
    }

    public function start()
    {
        foreach ($this->topics as $topic) {
            /** @var Topic $topic*/
            foreach ($topic->getChannels() as $channel) {
                /** @var Channel $channel */
                foreach ($channel->getClients() as $client) {
                    /** @var Client $client */
                    $client->start();
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
}