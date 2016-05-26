<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\Types\Arr;

class Log
{

    private static $instances = [];
    private $config;

    private static function getDefaultConfig()
    {
        return [
            'factory' => '',
            'app' => Application::getInstance()->getName(),
            'module' => 'default',
            'logLevel' => 'debug',
            'storeType' => 'normal',
            'path' => 'debug.log',
            'useBuffer' => false,
            'bufferSize' => 4096,
            'async' => true,
            'format' => ''
        ];
    }

    public function __construct($key)
    {
        $this->configParser($key);
        $logger = $this->adapter();
        if ($this->config['useBuffer']) {
            $logger = $this->bufferDecorate($logger);
        }
        return $logger;
    }

    private function configParser($key)
    {
        if (!$key) {
            throw new InvalidArgumentException('Configuration key cannot be null');
        }

        $logUrl = Config::get('log.' . $key);
        if (!$logUrl) {
            throw new InvalidArgumentException('Can not find config for logKey: ' . $key);
        }

        $config = parse_url($logUrl);
        $defaults = self::getDefaultConfig();
        $defaults['factory'] = $config['scheme'];
        $defaults['logLevel'] = $config['host'];
        if (isset($config['path'])) {
            $defaults['path'] = $config['path'];
        }

        parse_str($config['query'], $params);
        $result = Arr::merge($defaults, $params);

        if (isset($result['module'])) {
            $result['module'] = $key;
        }

        if (isset($result['format'])) {
            $result['format'] = strtolower($result['format']);
        }

        $this->config = $result;
    }

    private function adapter()
    {
        $logger = null;
        $config = $this->config;
        switch ($config['factory']) {
            case 'syslog':
                $logger = new SystemLogger($config);
                break;
            case 'file':
            case 'log':
                $logger = new FileLogger($config);
                break;
            case 'blackhole':
                $logger = new BlackholeLogger($config);
                break;
            default:
                throw new InvalidArgumentException('Cannot support this pattern');
        }

        return $logger;
    }

    private function bufferDecorate($logger)
    {
        return new BufferLogger($logger, $this->config);
    }

    public static function getInstance($key)
    {
        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }
        self::$instances[$key] = new self($key);
        return self::$instances[$key];
    }

}
