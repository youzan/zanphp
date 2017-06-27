<?php

namespace Zan\Framework\Network\MqSubscribe\Subscribe;

class Channel
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var array Client[]
     */
    private $clients = [];
    
    private $totalMsgCount = 0;
    
    public function __construct($name, Topic $topic)
    {
        $this->name = $name;
        $this->topic = $topic;
    }

    /**
     * 获取Channel名字
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取所属topic实例
     * 
     * @return Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * 初始化Client
     * 
     * @param array $config
     */
    public function initClient(array $config)
    {
        $client = new Client($config, $this);
        
        $this->clients[] = $client;
    }

    /**
     * 获取当前Channel下Client列表
     * 
     * @return array Client[]
     */
    public function getClients()
    {
        return $this->clients;
    }
    
    public function incrMsgCount()
    {
        $this->totalMsgCount++;
        $this->topic->incrMsgCount();
    }
}