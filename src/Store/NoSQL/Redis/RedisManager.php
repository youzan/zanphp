<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/3/9
 * Time: 14:11
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
