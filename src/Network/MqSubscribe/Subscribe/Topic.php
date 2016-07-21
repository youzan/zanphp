<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

class Topic
{
    private $name;
    
    /** @var  Manager */
    private $manager;
    
    private $channels = [];
    
    private $totalMsgCount = 0;
    
    public function __construct($name, $manager)
    {
        $this->name = $name;
        $this->manager = $manager;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function initChannel($name, $config)
    {
        $channel = new Channel($name, $this);

        for ($i = 0; $i < $config['num']; $i++) {
            $channel->initClient($config);
        }

        $this->channels[$name] = $channel;
    }
    
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