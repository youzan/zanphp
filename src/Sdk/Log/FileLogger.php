<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/24
 * Time: 下午2:55
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Utilities\Types\Time;

class FileLogger extends BaseLogger
{

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->config['path'] = $this->getLogPath($this->config);
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

    public function init()
    {
//        $this->writer = new FileWriter($this->config['path'], $this->config['async']);
        $this->writer = FileWriter::getInstance($this->config['path'], $this->config['async']);
    }

    public function format($level, $message, $context)
    {
        $result = '';
        $config = $this->config;
        $time = Time::current('Y-m-d H:i:s.u');
        $level = strtoupper($level);
        $app = $config['app'];
        $module = $config['module'];
        $log = $this->getLogString($message, $context);
        sprintf("[%s]\t[%s]\t[%s]\t%s\t%s\n", $time, $level, $app, $module, $log);
        return $result;
    }

    private function getLogString($message, $data)
    {
        $result = $message;
        if (!$data || !is_array($data) || !is_object($data)) {
            return $result;
        }
        $format = isset($this->config['format']) ? $this->config['format'] : '';
        switch ($format) {
            case 'json':
                $result .= "\t" . json_encode($data, JSON_UNESCAPED_UNICODE);
                break;
            case 'var':
                $result .= "\t" . var_export($data, true);
                break;
            default :
                break;
        }
        return $result;
    }

}
