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
    
    public function __construct($name, Topic $topic)
    {
        $this->name = $name;
        $this->topic = $topic;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function getTopic()
    {
        return $this->topic;
    }

    public function initClient($config)
    {
        $client = new Client($config, $this);
        
        $this->clients[] = $client;
    }
    
    public function getClients()
    {
        return $this->clients;
    }
}