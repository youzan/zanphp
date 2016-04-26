<?php

/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/4/26
 * Time: 下午4:57
 */

namespace Zan\Framework\Network\Monitor;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use  Zan\Framework\Network\Http\Server;

use Zan\Framework\Network\Server\Timer\Timer;


class Worker
{
    use Singleton;

    public $classHash;
    public $workerId;
    public $service;
    public $config;

    public function init($workerId,$service,$config){
        if(!is_array($config)){
            return ;
        }
        echo "init WorkerMonitor workerId:{$workerId}\n";
        $this->classHash = spl_object_hash($this);
        $this->workerId = $workerId;
        $this->service = $service;
        $this->config = $config;

        $this->restart();
        $this->checkStart();
    }

    public function restart()
    {
        $time = isset($this->config['max_live_time'])?$this->config['max_live_time']:0;

        if($time){
            Timer::after($time, $this->classHash,[$this,'closeWorker']);
        }
    }

    public function checkStart(){
        $checkInfo = $this->config['check'];
        $time = $checkInfo['time'];
        Timer::tick($time, $this->classHash.'check',[$this,'check']);
    }

    public function check(){
        $memory_limit = $this->config['check']['memory_limit'];
        $memory =  memory_get_usage();
        echo "check:workerId:{$this->workerId},memory:{$memory}\n";
        if($memory > $memory_limit){
            $this->closeWorker();
        }
    }


    public function closeWorker()
    {
        echo "close:workerId:{$this->workerId}\n";
        /* @var $this->service Server */
        $this->service->swooleServer->stop();
    }

}