<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Utilities\Types\Time;

class FileLogger extends BaseLogger
{

    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->config['path'] = $this->getLogPath($this->config);
        $this->writer = new FileWriter($this->config['path'], $this->config['async']);
    }

    private function getLogPath($config)
    {
        $logBasePath = '';
        $path = ltrim($config['path'], '/');

        if ($config['factory'] === 'log') {
            $logBasePath = Config::get('path.log');
        } else if ($config['factory'] === 'file') {
            $logBasePath = '/';
        }

        $path = $logBasePath . $path;

        return $path;
    }

    public function format($level, $message, $context)
    {
        $config = $this->config;
        $time = Time::current('Y-m-d H:i:s.u');
        $level = strtoupper($level);
        $app = $config['app'];
        $module = $config['module'];
        $log = $this->getLogString($message, $context);

        $result = sprintf("[%s]\t[%s]\t[%s]\t%s\t%s\n", $time, $level, $app, $module, $log);
        return $result;
    }

    private function getLogString($message, $context)
    {
        $result = $message;
        if (empty($context)) {
            return $result;
        }

        $detail = [];
        if (isset($context['exception'])) {
            $e = $context['exception'];
            if ($e instanceof \Throwable || $e instanceof \Exception) {
                $detail['error'] = $this->formatException($e);
                unset($context['exception']);
            }
        }
        
        $detail['extra'] = $context;
        $format = isset($this->config['format']) ? $this->config['format'] : 'json';

        switch ($format) {
            case 'json':
                $result = $result . "\t" . json_encode($detail, JSON_UNESCAPED_UNICODE);
                break;
            case 'var':
                $result = $result . "\t" . var_export($result, true);
                break;
            default :
                break;
        }

        return $result;
    }

}
