<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Core\Env;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Connection\ConnectionEx;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Network\Connection\Factory\Syslog;

class SystemLogger extends BaseLogger
{
    const TOPIC_PREFIX = 'log';
    private $priority;
    private $hostname;
    private $server;
    private $pid;
    private $conn;
    private $connectionConfig;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->connectionConfig = 'syslog.' . str_replace('/', '', $this->config['path']);
        $this->priority = LOG_LOCAL3 + LOG_INFO;
        $this->hostname = Env::get('hostname');
        $this->server = $this->hostname . '/' . Env::get('ip');
        $this->pid = Env::get('pid');
    }

    public function init()
    {
        $this->conn = (yield ConnectionManager::getInstance()->get($this->connectionConfig));
        if ($this->conn instanceof ConnectionEx) {
            $this->conn->release();
            $this->writer = new SystemWriterEx($this->connectionConfig);
        } else {
            $this->writer = new SystemWriter($this->conn);
        }
    }

    public function format($level, $message, $context)
    {
        // 业务需求：flume 系统识别的是 warn
        $level = ($level === 'warning') ? 'warn' : $level;

        $header = $this->buildHeader($level);
        $topic = $this->buildTopic();
        $body = $this->buildBody($level, $message, $context);
        $result = $header . 'topic=' . $topic . ' ' . $body;

        return $result;
    }

    protected function doWrite($log)
    {
        try {
            if (!$this->writer) {
                yield $this->init();
            }

            yield $this->getWriter()->write($log);
        } catch (\Throwable $t) {
            echo_exception($t);
        } catch (\Exception $ex) {
            echo_exception($ex);
        }
    }

    private function buildHeader($level)
    {
        $time = date('Y-m-d H:i:s');
        return "<{$this->priority}>{$time} {$this->server} {$level}[{$this->pid}]: ";
    }

    private function buildTopic()
    {
        $config = $this->config;
        if ($config['module'] == 'soa-framework') {
            $result = SystemLogger::TOPIC_PREFIX . '.soa-framework.default';
        } else {
            $result = SystemLogger::TOPIC_PREFIX . '.' . $config['app'] . '.' . $config['module'];
        }
        return $result;
    }

    private function buildBody($level, $message, array $context = [])
    {
        $detail = [];
        if (isset($context['exception'])) {
            $e = $context['exception'];
            if ($e instanceof \Throwable || $e instanceof \Exception) {
                $e = $context['exception'];
                $detail['error'] = $this->formatException($e);
                $context['exception_metadata'] = $e instanceof ZanException ? $e->getMetadata() : [];
                unset($context['exception']);
            }
        }

        $detail['extra'] = $context;
        $result = [
            'platform' => 'php',
            'app' => $this->config['app'],
            'module' => $this->config['module'],
            'level' => $level,
            'tag' => $message,
            'detail' => $detail
        ];

        if ($this->config['module'] == 'soa-framework') {
            $result['app'] = 'soa-framework';
            $result['module'] = 'default';
        }

        return json_encode($result);
    }

}
