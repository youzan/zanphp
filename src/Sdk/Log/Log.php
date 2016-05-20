<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Sdk\Log\Track\LoggerFile;
use Zan\Framework\Sdk\Log\Track\LoggerSystem;
use Psr\Log\LoggerInterface;
use Zan\Framework\Foundation\Exception\ZanException;

class Log
{
    private $config = [
        'factory'   => '',
        'app'       => 'zanphp',
        'level'     => 'debug',
        'module'    => 'default',
        'type'      => 'normal',
        'path'      => 'debug.log',
    ];

    /**
     * @var LoggerInterface
     */
    private static $instance;

    public function __construct($config){
        $this->configParser($config);
        return $this->adapter();
    }

    /**
     * 日志配置解析
     * @param $key
     * @throws ZanException
     */
    private function configParser($key){
        if(!$key){
            throw new InvalidArgumentException('Configuration key cannot be null');
        }

        $logUrl = Config::get('log.'.$key);
        if(!$logUrl){
            throw new InvalidArgumentException('Can not find config for logKey: ' . $key);
        }

        $config = parse_url($logUrl);
        parse_str($config['query'], $ps);

        $this->config['factory']    = $config['scheme'];
        $this->config['level']      = $config['host'];
        $this->config['path']       = isset($config['path']) ? $config['path'] : $this->config['path'];
        $this->config['module']     = isset($ps['module']) ? $ps['module'] : $this->config['module'];
        $this->config['type']       = isset($ps['type']) ? $ps['type'] : $this->config['type'];

    }

    /**
     * 适配器
     * @return mixed
     * @throws ZanException
     */
    private function adapter(){
        $factory = $this->config['factory'];
        switch($factory){
            case "syslog":
                return new LoggerSystem($this->config);
                break;
            case "log":
                return new LoggerFile($this->config);
                break;
            default:
                throw new InvalidArgumentException('Cannot support this pattern');
        }
    }

    /**
     * 多实例
     * @return LoggerInterface
     */
    public static function make($key){
        if (isset(self::$instance[$key])) {
           return self::$instance[$key];
        }
        self::$instance[$key] = new self($key);
        return self::$instance[$key];
    }
}