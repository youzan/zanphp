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
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Sdk\Trace\Trace;

class Queue implements Async
{
    /**
     * @var callable
     */
    private $handler = null;

    /** @var  Trace */
    private $trace;

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
     * @param MsgInterface $message
     * @return $this
     */
    public function publish($topic, MsgInterface $message)
    {
        $this->trace = (yield getContext('trace'));

        $this->handler = function ($callback) use ($topic, $message) {
            if ($this->trace) {
                $this->trace->transactionBegin(Constant::NSQ_PUB, $topic);
            }

            NSQueue::publish($topic, $message, $this->getResultCallback($callback), $this->getExceptionCallBack($callback));
        };
        
        yield $this;
    }

    /**
     * @param $topic
     * @param $channel
     * @param callable $callback
     * @param int $timeout
     * @return $this
     */
    public function subscribe($topic, $channel, callable $callback, $timeout = 1800)
    {
        $this->handler = function ($cb) use ($topic, $channel, $callback, $timeout) {
            NSQueue::set(['subTimeout' => $timeout]);
            NSQueue::subscribe($topic, $channel, $callback);
            
            call_user_func($cb, [false, null]);
        };

        yield $this;
    }

    private function getResultCallback($callback)
    {
        return function ($response) use ($callback) {
            if ($this->trace) {
                if ($this->isOk($response)) {
                    $this->trace->commit(Constant::SUCCESS);
                } else {
                    $this->trace->commit(json_encode($response));
                }
            }

            call_user_func($callback, $response);
        };
    }

    private function getExceptionCallBack($callback)
    {
        return function (\Exception $exception) use ($callback) {
            if ($this->trace) {
                $this->trace->commit($exception->getTraceAsString());
            }

            call_user_func_array($callback, [null, $exception]);
        };
    }

    private function isOk($response)
    {
        if (isset($response['result']) and $response['result'] === 'ok') {
            return true;
        }
        return false;
    }
}