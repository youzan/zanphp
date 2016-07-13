<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\Types\Arr;

class Log
{
    private static $instances = [];

    /**
     * @param $key
     * @return BlackholeLogger|FileLogger|SystemLogger
     * @throws InvalidArgumentException
     */
    public static function make($key)
    {
        if (!$key) {
            throw new InvalidArgumentException('Configuration key cannot be null');
        }

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        $logger = self::create($key);
        self::$instances[$key] = $logger;

        return self::$instances[$key];
    }

    /**
     * @param $key
     * @return null|BlackholeLogger|BufferLogger|FileLogger|SystemLogger
     * @throws InvalidArgumentException
     */
    private static function create($key)
    {
        $config = self::getConfigByKey($key);
        $logger = self::getAdapter($config);

        if ($config['useBuffer']) {
            $logger = new BufferLogger($logger, $config);
        }

        return $logger;
    }

    private static function getDefaultConfig()
    {
        return [
            'factory' => '',
            'module' => null,
            'level' => 'debug',
            'storeType' => 'normal',
            'path' => 'debug.log',
            'useBuffer' => false,
            'bufferSize' => 4096,
            'async' => true,
            'format' => 'json'
        ];
    }

    private static function getConfigByKey($key)
    {
        $logUrl = Config::get('log.' . $key, null);
        if (!$logUrl) {
            throw new InvalidArgumentException('Can not find config for logKey: ' . $key);
        }

        $config = parse_url($logUrl);
        $result = self::getDefaultConfig();
        $result['factory'] = $config['scheme'];
        $result['level'] = $config['host'];
        if (isset($config['path'])) {
            $result['path'] = $config['path'];
        }

        if (isset($config['query'])) {
            parse_str($config['query'], $params);
            $params = self::fixBooleanValue($params);
            $result = Arr::merge($result, $params);
        }

        if (!$result['module']) {
            $result['module'] = $key;
        }

        if (isset($result['format'])) {
            $result['format'] = strtolower($result['format']);
        }

        // force set app value to Application name
        $result['app'] = Application::getInstance()->getName();

        return $result;
    }

    private static function fixBooleanValue($params)
    {
        if (empty($params)) {
            return $params;
        }
        foreach ($params as $key => $val) {
            if ($val == "true") {
                $params[$key] = true;
            } else if ($val == "false") {
                $params[$key] = false;
            }
        }
        return $params;
    }

    /**
     * @param $config
     * @return null|BlackholeLogger|FileLogger|SystemLogger
     * @throws InvalidArgumentException
     */
    private static function getAdapter($config)
    {
        $logger = null;
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

}
