<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

class Client
{
    private $consumer;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var Channel
     */
    private $channel;
    
    private $sum;

    public function __construct($consumer, Channel $channel)
    {
        $this->consumer = $consumer;
        $this->channel = $channel;
        $this->topic = $channel->getTopic();
    }
}