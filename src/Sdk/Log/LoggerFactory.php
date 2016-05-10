<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Sdk\Log\Track\LoggerFile;
use Zan\Framework\Sdk\Log\Track\LoggerSystem;
use Psr\Log\LoggerInterface;
use Zan\Framework\Foundation\Exception\ZanException;

class LoggerFactory
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
        $this->adapter();
    }

    /**
     * 日志配置解析
     * @param $key
     * @throws ZanException
     */
    private function configParser($key){
        if(!$key){
            throw new ZanException('Configuration key cannot be null');
        }
        //$logUrl = Config::get('log'.$key);

        //test log url
        $logUrl = "log://error/error.log?module=trade";

        if(!$logUrl){
            throw new ZanException('Configuration cannot be null');
        }

        $config = parse_url($logUrl);
        parse_str($config['query'], $ps);

        $this->config['factory']    = $config['scheme'];
        $this->config['level']      = $config['host'];
        $this->config['module']     = isset($ps['module']) ? $ps['module'] : $this->config['module'];
        $this->config['type']       = isset($ps['type']) ? $ps['type'] : $this->config['type'];
        $this->config['path']       = isset($ps['path']) ? $ps['path'] : $this->config['path'];
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
                self::$instance = new LoggerSystem($this->config);
                break;
            case "log":
                self::$instance = new LoggerFile($this->config);
                break;
            default:
                throw new ZanException('Cannot support this pattern');
        }
    }

    /**
     * 单例
     * @return LoggerInterface
     */
    public static function getInstance($config){
        if (!self::$instance) {
           new self($config);
        }
        return self::$instance;
    }
}