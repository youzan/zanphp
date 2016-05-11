<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/12
 * Time: 22:23
 */
namespace Zan\Framework\Store;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Store\NoSQL\Redis\RedisManager;

class Cache {

    private $redis=null;

    public function __construct($connection) {
        $this->redis = new RedisManager($connection);
    }

    public function get($key)
    {
        $realKey = Config::get($key);
        if (!empty($realKey)) {
            $result = (yield $this->redis->get($realKey['key']));
            yield $result;
        }
    }

    public function set($key, $value)
    {
        $realKey = Config::get($key);
        if (!empty($realKey)) {
            $result = (yield $this->redis->set($realKey['key'], $value));
            yield $result;
        }
    }
}