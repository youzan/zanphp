<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/6/14
 * Time: 下午2:48
 */

namespace Zan\Framework\Store\NoSQL\Redis;


use Zan\Framework\Foundation\Contract\Async;


/**
 * Class Redis
 * @method string get(string $key);
 * @method bool set(string $key, string $value);
 * @method array mGet(...$keys);
 * @method bool hSet(string $key, string $field, string $value);
 * @method string hGet(string $key, string $field);
 * @method bool expire(string $key, int $ttlSec);
 */
class Redis implements Async
{
    private $callback;
    private $conn;
    private $sock;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->sock = $conn->getSocket();
    }

    public function __call($name, $arguments)
    {
        $arguments[] = [$this, 'recv'];
        call_user_func_array([$this->sock, $name], $arguments);
        yield $this;
    }

    public function recv($client, $ret)
    {
        call_user_func($this->callback, $ret);
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }
}