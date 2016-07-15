<?php
namespace Zan\Framework\Network\MqSubscribe\Subscribe;

class Channel
{
    private $name;

    /**
     * @var Topic
     */
    private $topic;

    private $clients = [];

    private $sum;

    public function __construct($name, Topic $topic)
    {
        $this->name = $name;
        $this->topic = $topic;
    }
    
    public function getTopic()
    {
        return $this->topic;
    }

    public function initClient($consumer)
    {
        $client = new Client($consumer, $this);
        
        $this->clients[] = $client;
    }
}