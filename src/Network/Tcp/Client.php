<?php

namespace Zan\Framework\Network\Tcp;

use \swoole_client as TcpClient;
use Zan\Framework\Network\Exception\ConnectionException;

class Client implements \Zan\Framework\Network\Contract\Client{

    /**
     * @var TcpClient
     */
    private $client;

    private $connected = false;
    private $reconnect = false;

    public function __construct($config=[])
    {
        $this->client = new TcpClient($config['keep_alive'] ? SWOOLE_SOCK_TCP | SWOOLE_KEEP : SWOOLE_TCP);
        $this->connect($config);
    }

    public function connect($config)
    {
        $connected = $this->client->connect($config['host'], $config['port'], $config['timeout']);
        if (false == $connected) {
            throw new ConnectionException(socket_strerror($this->client->errCode), $this->client->errCode);
        }
        $this->setConnected();
    }

    public function send($data)
    {

    }

    public function receive()
    {
        $data = $this->client->recv();
    }

    public function isConnected()
    {
        if (!$this->client->isConnected()) {
            $this->setConnected(false);
        }
    }

    public function setConnected()
    {
        $this->connected = true;
    }

    public function close()
    {
        if (!$this->connected) return true;

        return $this->client->close();
    }

}