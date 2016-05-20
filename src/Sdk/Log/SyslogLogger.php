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

class SyslogLogger extends BaseLogger implements LoggerInterface {
    /**
     * @var TrackLogger
     */
    private $track;

    public function __construct($config){
        $this->init($config);

        $this->track = Track::get($this->app, $this->module, $this->type);
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
            return $this->track->error($message);
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
            return $this->track->warn($message);
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
            return $this->track->info($message);
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
            return $this->track->debug($message);
        }
    }

    public function log($level,$message, array $context = array()){

    }
} 