<?php
/**
 * Created by PhpStorm.
 * User: haitao
 * Date: 16/3/17
 * Time: 13:33
 */

namespace Zan\Framework\Sdk\Log;

use Psr\Log\LogLevel;

abstract class BaseLogger {
    protected $app;
    protected $module;
    protected $type;
    protected $level;
    protected $leveNum = 0;
    protected $path;
    protected $logMap =[
        'debug'     => 0,
        'info'      => 1,
        'notice'    => 2,
        'warning'   => 3,
        'error'     => 4,
        'critical'  => 5,
        'alert'     => 6,
        'emergency' => 7,
    ];
    protected $writer = null;

    /**
     * 初始化
     * @param $config
     */
    public function init($config){
        $this->app      = $config['app'];
        $this->module   = $config['module'];
        $this->type     = $config['type'];
        $this->level    = $config['level'];
        $this->levelNum = $this->getLevelNum($this->level);
        $this->path     = $config['path'];
    }

    public function emergency($message, array $context = []){}
    public function alert($message, array $context = []){}
    public function critical($message, array $context = []){}

    /**
     * error
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function error($message, array $context = []){
        if($this->checkLevel(LogLevel::ERROR)){
            return $this->writer->write($message, LogLevel::ERROR);
        }
    }

    /**
     * warning
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function warning($message, array $context = []){
        if($this->checkLevel(LogLevel::WARNING)){
            return $this->writer->write($message, LogLevel::WARNING);
        }
    }

    /**
     * notice
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function notice($message, array $context = []){
        if($this->checkLevel(LogLevel::NOTICE)){
            return $this->writer->write($message, LogLevel::NOTICE);
        }
    }

    /**
     * info
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function info($message, array $context = []){
        if($this->checkLevel(LogLevel::INFO)){
            return $this->writer->write($message, LogLevel::INFO);
        }
    }

    /**
     * debug
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function debug($message, array $context = []){
        if($this->checkLevel(LogLevel::DEBUG)){
            return $this->writer->write($message, LogLevel::DEBUG);
        }
    }

    public function log($level, $message, array $context = [])
    {

    }

    /**
     * 检查等级
     * @param $funcLevel
     * @return bool
     */
    public function checkLevel($funcLevel){
        $funcLevelNum   = $this->getLevelNum($funcLevel);
        if($this->leveNum >= $funcLevelNum){
            return true;
        }
        return false;
    }

    /**
     * get level num
     * @param $level
     * @return mixed
     */
    protected function getLevelNum($level){
        return $this->logMap[$level];
    }
} 