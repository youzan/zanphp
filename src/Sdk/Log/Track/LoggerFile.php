<?php
/**
 * Created by PhpStorm.
 * User: haitao
 * Date: 16/3/16
 * Time: 17:04
 */

namespace Zan\Framework\Sdk\Log\Track;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerFile extends LoggerBase implements LoggerInterface {

    private $track;

    public function __constract($config){
        $this->init($config);
        $this->track = new TrackFile($this->app, $this->module, $this->type, $this->path);
    }

    public function emergency($message, array $context = array()){}
    public function alert($message, array $context = array()){}
    public function critical($message, array $context = array()){}

    /**
     * error
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function error($message, array $context = array()){
        if($this->checkLevel(LogLevel::ERROR)){
            $this->track->error($message);
        }
    }

    /**
     * warning
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function warning($message, array $context = array()){
        if($this->checkLevel(LogLevel::WARNING)){
            $this->track->warn($message);
        }
    }

    /**
     * notice
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function notice($message, array $context = array()){

    }

    /**
     * info
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function info($message, array $context = array()){
        if($this->checkLevel(LogLevel::INFO)){
            $this->track->info($message);
        }
    }

    /**
     * debug
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function debug($message, array $context = array()){
        if($this->checkLevel(LogLevel::DEBUG)){
            $this->track->debug($message);
        }
    }

    public function log($level,$message, array $context = array()){

    }
} 