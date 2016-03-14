<?php

namespace Zan\Framework\Sdk\Log\Track;

use Exception;

class LoggerAppender
{
    const TOPIC_PREFIX = "track";
    private $appenderType;
    private $logType;
    private $priority;
    private $hostname;
    private $server;
    private $pid;
    private $topic;

    public function __construct($appenderType, $logType, $topic)
    {
        $this->appenderType = $appenderType;
        $this->logType = $logType;
        $this->topic = $topic;
        $this->priority = LOG_LOCAL3 + LOG_INFO;
        $this->hostname = gethostname();
        $this->server = $this->hostname . "/" . gethostbyname($this->hostname);
        $this->pid = getmypid();
    }

    public function append($app, $module, $level, $msg, Exception $e = null, $extra = null)
    {
        $header = $this->buildHeader($level);

        $tag = $this->buildTag();

        $body = $this->bulidBody($app, $module, $level, $msg, $e, $extra);

        $log = $header . "topic=" . $tag . " " . $module . " " . $body;

        $trackSender = LoggerTCPSender::getInstance();
        $trackSender->send($log);
    }

    private function buildHeader($level)
    {
        $time = date("Y-m-d H:i:s");
        return "<{$this->priority}>{$time} {$this->server} {$level}[{$this->pid}]: ";
    }

    private function buildTag()
    {
        if ($this->topic) {
            return LoggerAppender::TOPIC_PREFIX . "." . $this->appenderType . "." . $this->topic;
        }
        return LoggerAppender::TOPIC_PREFIX . "." . $this->appenderType;
    }

    private function bulidBody($app, $module, $level, $msg, Exception $e = null, $extra = null)
    {
        $detail = [];
        if ($e) {
            $detail['error'] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'param' => $e->getTrace()[0]['args'],
                'stacktraces' => $e->getTraceAsString()
            ];
        }
        if ($extra) {
            $detail['extra'] = $extra;
        }
        $log = [
            'type' => $this->logType,
            'platform' => 'php-' . PHP_VERSION,
            'tag' => $msg,
            'app' => $app,
            'module' => $module,
            'detail' => $detail,
            'level' => $level
        ];
        return json_encode($log);
    }
}