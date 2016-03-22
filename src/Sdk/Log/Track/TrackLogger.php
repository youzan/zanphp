<?php

namespace Zan\Framework\Sdk\Log\Track;

use Exception;

class TrackLogger extends Logger
{
    /**
     * @var LoggerAppender
     */
    protected $appender;

    public function debug($msg, Exception $e = null, $extra = null)
    {
        yield $this->appender->append($this->app, $this->module, "debug", $msg, $e, $extra);
    }

    public function info($msg, Exception $e = null, $extra = null)
    {
        yield $this->appender->append($this->app, $this->module, "info", $msg, $e, $extra);
    }

    public function warn($msg, Exception $e = null, $extra = null)
    {
        yield $this->appender->append($this->app, $this->module, "warn", $msg, $e, $extra);
    }

    public function error($msg, Exception $e = null, $extra = null)
    {
        yield $this->appender->append($this->app, $this->module, "error", $msg, $e, $extra);
    }

    protected function init()
    {
        yield $this->appender = new LoggerAppender(AppenderType::normal, $this->type, $this->topic);
    }
}