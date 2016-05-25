<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\Types\Arr;

class Log
{

    public static $instances = [];

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

    private static function configParser($key)
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
        $defaults['key'] = $key;
        $defaults['factory'] = $config['scheme'];
        $defaults['logLevel'] = $config['host'];
        if (isset($config['path'])) {
            $defaults['path'] = $config['path'];
        }

        parse_str($config['query'], $params);
        $result = Arr::merge($defaults, $params);

        if (isset($result['format'])) {
            $result['format'] = strtolower($result['format']);
        }

        return $result;
    }

    public static function getInstance($key = 'debug')
    {
        $config = self::configParser($key);

        $logger = null;
        switch ($config['factory']) {
            case 'syslog':
                $logger = self::initLoggerInstance($config);
                break;
            case 'file':
            case 'log':
            case 'blackhole':
                $logger = self::getLoggerInstance($config);
                break;
            default:
                throw new InvalidArgumentException('Cannot support this pattern');
        }

        return $logger;
    }

    private static function initLoggerInstance($config)
    {
        return new Logger($config);
    }

    private static function getLoggerInstance($config)
    {
        $key = $config['key'];
        if (isset(Log::$instances[$key])) {
            return Log::$instances[$key];
        }
        $logger = self::initLoggerInstance($config);
        Log::$instances[$key] = $logger;
        return Log::$instances[$key];
    }

}
