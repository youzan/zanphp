<?php

namespace Zan\Framework\Network\Common;


use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Network\Common\Exception\DnsLookupTimeoutException;
use Zan\Framework\Network\Common\Exception\HostNotFoundException;
use Zan\Framework\Network\Server\Timer\Timer;

class DnsClient implements Async
{
    private $callback;
    private $host;
    const   MAX_RETRIES = 3;
    private $times = 0;
    private function __construct() { }

    public static function lookup($host, $timeout = 100)
    {
        $self = new static;
        $self->host = $host;
        $self->onTimeout($timeout);
        $self->resolve();
        return $self;
    }

    private function resolve()
    {
        // 无需做缓存, 内部有缓存
        swoole_async_dns_lookup($this->host, function($domain, $ip) {
            if ($this->callback) {
                Timer::clearAfterJob($this->timerId());
                if ($ip) {
                    call_user_func($this->callback, $ip);
                } else {
                    $ex = new HostNotFoundException("", 408, null, [ "host" => $domain ]);
                    call_user_func($this->callback, null, $ex);
                }
                unset($this->callback);
            }
            $this->times = 0;
        });
    }

    private function onTimeout($duration)
    {
        Timer::after($duration, function() {
            $maxRetries = static::MAX_RETRIES;
            if ($this->times++ < $maxRetries)
                return;
            $this->times = 0;
            if ($this->callback) {
                $ex = new DnsLookupTimeoutException("dns lookup timeout after $maxRetries times", 408, null, ["host" => $this->host]);
                call_user_func($this->callback, $this->host, $ex);
                unset($this->callback);
            }
        }, $this->timerId());
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    private function timerId()
    {
        return spl_object_hash($this) . "_dns_lookup";
    }
}