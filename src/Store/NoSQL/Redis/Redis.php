<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/6/14
 * Time: 下午2:48
 */

namespace Zan\Framework\Store\NoSQL\Redis;


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
 */
class Redis implements Async
{
    private $callback;
    private $conn;
    private $sock;

    const DEFAULT_CALL_TIMEOUT = 2000;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->sock = $conn->getSocket();
    }

    public function __call($name, $arguments)
    {
        $arguments[] = [$this, 'recv'];
        call_user_func_array([$this->sock, $name], $arguments);
        $this->beginTimeoutTimer($name, $arguments);
        yield $this;
    }

    public function recv($client, $ret)
    {
        $this->cancelTimeoutTimer();
        call_user_func($this->callback, $ret);
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    private function beginTimeoutTimer($name, $args)
    {
        $config = $this->conn->getConfig();
        $timeout = isset($config['timeout']) ? $config['timeout'] : self::DEFAULT_CALL_TIMEOUT;
        Timer::after($timeout, $this->onTimeout($name, $args), spl_object_hash($this));
    }

    private function cancelTimeoutTimer()
    {
        Timer::clearAfterJob(spl_object_hash($this));
    }

    private function onTimeout($name, $args)
    {
        $start = microtime(true);
        return function() use($name, $args, $start) {
            // TODO TRACE

            if ($this->callback) {
                $duration = microtime(true) - $start;
                $ctx = [
                    "name" => $name,
                    "args" => $args,
                    "duration" => $duration,
                ];
                call_user_func_array($this->callback, [null, new RedisCallTimeoutException("Redis call $name timeout", 0, null, $ctx)]);
            }
        };
    }
}