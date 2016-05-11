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

    use Singleton;

    private $conn = null;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function get($key) {
        $result = new RedisResult();
        $this->conn->get($key, [$result, 'response']);

        yield $result;
    }

    public function set($key, $value) {
        $result = new RedisResult();
        $this->conn->set($key, $value, [$result, 'response']);

        yield $result;
    }

}
