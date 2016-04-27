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

    /**
     * @var TrackFile
     */
    private $track;

    public function __construct($config){
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
            return $this->track->doWrite($message, LogLevel::ERROR);
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
            return $this->track->doWrite($message, LogLevel::WARNING);
        }
    }

    /**
     * notice
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function notice($message, array $context = array()){
        if($this->checkLevel(LogLevel::NOTICE)){
            return $this->track->doWrite($message, LogLevel::NOTICE);
        }
    }

    /**
     * info
     * @param string $message
     * @param array $context
     * @return null|void
     */
    public function info($message, array $context = array()){
        if($this->checkLevel(LogLevel::INFO)){
            return $this->track->doWrite($message, LogLevel::INFO);
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
            return $this->track->doWrite($message, LogLevel::DEBUG);
        }
    }

    public function log($level,$message, array $context = array()){

    }
} 