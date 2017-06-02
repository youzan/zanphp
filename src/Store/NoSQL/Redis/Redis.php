<?php

namespace Zan\Framework\Store\NoSQL\Redis;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Store\NoSQL\Exception\RedisCallTimeoutException;

/**
 * Class Redis
 * @method string get(string $key);
 * @method bool set(string $key, string $value);
 * @method array mGet(...$keys);
 * @method int hSet(string $key, string $field, string $value);
 * @method string hGet(string $key, string $field);
 * @method string hDel(string $key, string $field);
 * @method bool expire(string $key, int $ttlSec);
 * @method int incr(string $key);
 * @method int incrBy(string $key, int $value);
 * @method int hIncrBy(string $key, string $field, int $value);
 * @method bool del(string $key);
 * @method bool hMGet(string $key, ...$params);
 * @method bool hMSet(string $key, ...$params);
 */
class Redis implements Async
{
    private $callback;
    /**
     * @var \Zan\Framework\Network\Connection\Driver\Redis
     */
    private $conn;
    /**
     * @var \swoole_redis
     */
    private $sock;
    private $cmd;
    private $args;

    const DEFAULT_CALL_TIMEOUT = 2000;

    /**
     * Redis constructor.
     * @param Connection $conn
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->sock = $conn->getSocket();
    }

    public function __call($name, $arguments)
    {
        $this->cmd = $name;
        $this->args = $arguments;
        $arguments[] = [$this, 'recv'];
        $this->sock->$name(...$arguments);
        $this->beginTimeoutTimer();
        yield $this;
    }

    public function recv(/** @noinspection PhpUnusedParameterInspection */
        $client, $ret)
    {
        $this->cancelTimeoutTimer();
        call_user_func($this->callback, $ret);
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    private function beginTimeoutTimer()
    {
        $config = $this->conn->getConfig();
        $timeout = isset($config['timeout']) ? $config['timeout'] : self::DEFAULT_CALL_TIMEOUT;
        Timer::after($timeout, $this->onTimeout(), spl_object_hash($this));
    }

    private function cancelTimeoutTimer()
    {
        Timer::clearAfterJob(spl_object_hash($this));
    }

    private function onTimeout()
    {
        $start = microtime(true);
        return function() use($start) {
            if ($this->callback) {
                $duration = microtime(true) - $start;
                $ctx = [
                    "cmd" => $this->cmd,
                    "args" => $this->args,
                    "duration" => $duration,
                ];

                $callback = $this->callback;
                $ex = new RedisCallTimeoutException("Redis call {$this->cmd} timeout", 0, null, $ctx);
                $callback(null, $ex);
            }
        };
    }
}