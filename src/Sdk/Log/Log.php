<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\Types\Arr;

class Log
{

    private static $instances = [];

    private static function getDefaultConfig()
    {
        return [
            'factory' => '',
            'app' => Application::getInstance()->getName(),
            'module' => 'default',
            'level' => 'debug',
            'storeType' => 'normal',
            'path' => 'debug.log',
            'useBuffer' => false,
            'bufferSize' => 4096,
            'async' => true,
            'format' => ''
        ];
    }

    /**
     * @param $key
     * @return null|BlackholeLogger|BufferLogger|FileLogger|SystemLogger
     * @throws InvalidArgumentException
     */
    private static function instance($key)
    {
        $config = self::configParser($key);
        $logger = self::adapter($config);
        if ($config['useBuffer']) {
            $logger = self::bufferDecorate($logger, $config);
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
        $result = self::getDefaultConfig();
        $result['factory'] = $config['scheme'];
        $result['level'] = $config['host'];
        if (isset($config['path'])) {
            $result['path'] = $config['path'];
        }

        if (isset($config['query'])) {
            parse_str($config['query'], $params);
            $result = Arr::merge($result, $params);

            if (isset($result['module'])) {
                $result['module'] = $key;
            }

            if (isset($result['format'])) {
                $result['format'] = strtolower($result['format']);
            }
        }

        return $result;
    }

    /**
     * @param $config
     * @return null|BlackholeLogger|FileLogger|SystemLogger
     * @throws InvalidArgumentException
     */
    private static function adapter($config)
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

    private static function bufferDecorate($logger, $config)
    {
        return new BufferLogger($logger, $config);
    }

    /**
     * @param $key
     * @return BlackholeLogger|FileLogger|SystemLogger
     */
    public static function getInstance($key)
    {
        if (isset(self::$instances[$key])) {
            yield self::$instances[$key];
        }
        $logger = self::instance($key);
        yield $logger->init();
        self::$instances[$key] = $logger;
        yield self::$instances[$key];
    }

}
