<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/6/14
 * Time: 下午2:48
 */

namespace Zan\Framework\Store\NoSQL\Redis;


use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Sdk\Trace\ChromeTrace;
use Zan\Framework\Store\NoSQL\Exception\RedisCallTimeoutException;
use Zan\Framework\Utilities\DesignPattern\Context;


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
    private $conn;
    private $sock;
    private $cmd;
    private $args;

    /**
     * @var ChromeTrace
     */
    private $chromeTrace;

    const DEFAULT_CALL_TIMEOUT = 2000;

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
        $this->beginTimeoutTimer($name, $arguments);
        yield $this;
    }

    public function recv($client, $ret)
    {
        if ($this->chromeTrace instanceof ChromeTrace) {
            $this->chromeTrace->commit("info", $ret);
        }

        $this->cancelTimeoutTimer();
        call_user_func($this->callback, $ret);
    }

    public function execute(callable $callback, $task)
    {
        /** @var Task $task */
        /** @var Context $ctx */
        $ctx = $task->getContext();
        $chromeTrace = $ctx->get("chrome_trace", null);
        if ($chromeTrace instanceof ChromeTrace) {
            $req = [
                "cmd" => $this->cmd,
                "args" => $this->args,
            ];
            $conf = $this->conn->getConfig();
            if (isset($conf["path"])) {
                $req["dst"] = $conf["path"];
            } else if (isset($conf["host"]) && isset($conf["port"])) {
                $req["dst"] = "{$conf["host"]}:{$conf["port"]}";
            }
            $chromeTrace->beginTransaction("redis", $req);
            $this->chromeTrace = $chromeTrace;
        }

        $this->callback = $callback;
    }

    private function beginTimeoutTimer($name, $args)
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

                if ($this->chromeTrace instanceof ChromeTrace) {
                    $this->chromeTrace->commit("warn", $ctx);
                }

                $callback = $this->callback;
                $ex = new RedisCallTimeoutException("Redis call {$this->cmd} timeout", 0, null, $ctx);
                $callback(null, $ex);
            }
        };
    }
}