<?php

namespace Zan\Framework\Network\MqSubscribe\Subscribe;

class Topic
{
    private $name;
    
    /** @var  Manager */
    private $manager;

    /**
     * @var array Channel[channelName]
     */
    private $channels = [];
    
    private $totalMsgCount = 0;
    
    public function __construct($name, Manager $manager)
    {
        $this->name = $name;
        $this->manager = $manager;
    }

    /**
     * 获取Topic名字
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 初始化当前Topic下的Channel
     * 
     * @param $name
     * @param array $config
     */
    public function initChannel($name, array $config)
    {
        $channel = new Channel($name, $this);

        for ($i = 0; $i < $config['num']; $i++) {
            $channel->initClient($config);
        }

        $this->channels[$name] = $channel;
    }

    /**
     * 获取当前Topic下的Channel的Map
     * 
     * @return array Channel[channelName]
     */
    public function getChannels()
    {
        return $this->channels;
    }
    
    public function incrMsgCount()
    {
        $this->totalMsgCount++;
        $this->manager->incrMsgCount();
    }
}