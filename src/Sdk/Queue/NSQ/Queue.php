<?php
/**
 * NSQ Queue Client
 * User: moyo
 * Date: 4/13/16
 * Time: 2:10 PM
 */

namespace Zan\Framework\Sdk\Queue\NSQ;

use Kdt\Iron\NSQ\Message\MsgInterface;
use Kdt\Iron\NSQ\Queue as NSQueue;
use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class Queue implements Async
{
    use Singleton;

    /**
     * @var callable
     */
    private $handler = null;

    /**
     * Queue constructor.
     */
    public function __construct()
    {
        NSQueue::set([
            'lookupd' => Config::get('nsq.lookupd')
        ]);
    }

    /**
     * @param callable $callback
     */
    public function execute(callable $callback, $task)
    {
        call_user_func($this->handler, $callback);
    }

    /**
     * @param $topic
     * @param $message
     */
    public function publish($topic, MsgInterface $message)
    {
        $this->handler = function ($callback) use ($topic, $message) {
            NSQueue::publish($topic, $message, $callback);
        };
        
        yield $this;
    }

    /**
     * @param $topic
     * @param $channel
     * @param callable $callback
     * @param $timeout
     */
    public function subscribe($topic, $channel, callable $callback, $timeout = 1800)
    {
        $this->handler = function ($callback) use ($topic, $channel, $callback, $timeout) {
            NSQueue::set(['subTimeout' => $timeout]);
            NSQueue::subscribe($topic, $channel, $callback);
        };

        yield $this;
    }
}