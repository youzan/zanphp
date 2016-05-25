<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
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
        $this->client = new TcpClient($config['persistent'] ? SWOOLE_SOCK_TCP | SWOOLE_KEEP : SWOOLE_TCP);
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