<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/24
 * Time: 下午2:55
 */

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
        $result = [
            'message' => $message
        ];
        if (empty($context)) {
            return $result;
        }
        if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
            $result['error'] = [
                'code' => $context['exception']->getCode(),
                'message' => $context['exception']->getMessage(),
                'file' => $context['exception']->getFile(),
                'line' => $context['exception']->getLine(),
                'param' => $context['exception']->getTrace()[0]['args'],
                'stacktraces' => $context['exception']->getTraceAsString()
            ];
            unset($context['exception']);
            $this->config['format'] = 'json';
        }
        $result['extra'] = $context;
        $format = isset($this->config['format']) ? $this->config['format'] : '';
        switch ($format) {
            case 'json':
                $result .= "\t" . json_encode($result, JSON_UNESCAPED_UNICODE);
                break;
            case 'var':
                $result .= "\t" . var_export($result, true);
                break;
            default :
                break;
        }
        return $result;
    }

}
