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

namespace Zan\Framework\Store\NoSQL\Redis;

use Zan\Framework\Utilities\DesignPattern\Singleton;



class RedisManager {

    private $conn = null;

    private $client = null;

    public function __construct($connection) {
        $this->conn = $connection;
        $this->client = $connection->getSocket();
    }

    public function get($key) {
        $result = new RedisResult();
        $this->client->get($key, [$result, 'response']);
        $this->release();
        yield $result;
    }

    public function expire($key, $expire=0)
    {
        if ($expire<=0) {
            yield null;
            return;
        }
        $result = new RedisResult();
        $this->client->EXPIRE($key, $expire, [$result, 'response']);
        $this->release();
        yield $result;
    }

    public function set($key, $value, $expire=0) {
        $result = new RedisResult();
        $this->client->set($key, $value, [$result, 'response']);
        if ($expire >0) {
            $this->client->EXPIRE($key, $expire, [$result, 'response']);
        }
        $this->release();
        yield $result;
    }

    public function release()
    {
        $this->conn->release();
    }

}
