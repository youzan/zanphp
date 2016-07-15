<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

class Topic
{
    private $name;
    
    private $manager;
    
    private $channels = [];
    
    private $sum;
    
    public function __construct($name, $manager)
    {
        $this->name = $name;
        $this->manager = $manager;
    }

    public function initChannel($name, $config)
    {
        $channel = new Channel($name, $this);

        for ($i = 0; $i < $config['num']; $i++) {
            $channel->initClient($config['consumer']);
        }

        $this->channels[$name] = $channel;
    }
}