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

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function get($key) {
        $result = new RedisResult();
        $this->conn->get($key, [$result, 'response']);

        yield $result;
    }

    public function expire($key, $expire=0)
    {
        if ($expire<=0) {
            yield null;
            return;
        }
        $result = new RedisResult();
        $this->conn->EXPIRE($key, $expire, [$result, 'response']);
        yield $result;
    }

    public function set($key, $value, $expire=0) {
        $result = new RedisResult();
        $this->conn->set($key, $value, [$result, 'response']);
        if ($expire >0) {
            $this->conn->EXPIRE($key, $expire, [$result, 'response']);
        }
        yield $result;
    }

}
