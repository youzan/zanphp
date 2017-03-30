<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Network\Server\Timer\Timer;

class DnsClient
{
    const maxRetryCount = 3;

    private $callback;
    private $host;
    private $count;
    private $timeoutFn;
    private $timeout;

    private function __construct() { }

    public static function lookup($host, $callback = null, $timeoutFn = null, $timeout = 100)
    {
        $self = new static;
        $self->host = $host;
        $self->callback = $callback;
        $self->timeoutFn = $timeoutFn;
        $self->count = 0;
        if ($timeout <= 0)
            $timeout = 100;
        $self->timeout = $timeout;
        $self->resolve();
        return $self;
    }

    public static function lookupWithoutTimeout($host, $callback)
    {
        swoole_async_dns_lookup($host, $callback);
    }

    public function resolve()
    {
        $this->onTimeout($this->timeout);
        // 无需做缓存, 内部有缓存
        swoole_async_dns_lookup($this->host, function($host, $ip) {
            Timer::clearAfterJob($this->timerId());
            if ($this->callback) {
                call_user_func($this->callback, $host, $ip);
            }
        });
    }


    public function onTimeout($duration)
    {
        if ($this->count < self::maxRetryCount) {
            Timer::after($duration, [$this, "resolve"], $this->timerId());
            $this->count++;
        } else {
            Timer::after($duration, function() {
                if ($this->timeoutFn) {
                    call_user_func($this->timeoutFn);
                }
            }, $this->timerId());
        }
    }

    private function timerId()
    {
        return spl_object_hash($this) . "_dns_lookup";
    }
}