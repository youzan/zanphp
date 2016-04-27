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

    const GAP_TIME = 3000;

    public $classHash;
    public $workerId;
    public $server;
    public $config;

    /* @var $server Server */
    public function init($server,$config){
        if(!is_array($config)){
            return ;
        }

        $this->classHash = spl_object_hash($this);
        $this->server = $server;
        $this->workerId = $server->swooleServer->worker_id;
        $this->config = $config;

        $this->restart();
        $this->checkStart();
    }

    public function restart()
    {
        $time = isset($this->config['live_time'])?$this->config['live_time']:0;

        if($time){
            $time += $this->workerId * self::GAP_TIME;
            Timer::after($time, $this->classHash.'_restart',[$this,'closeWorker']);
        }
    }

    public function checkStart(){
        $time = $this->config['check_time'];
        Timer::tick($time, $this->classHash.'_check',[$this,'check']);
    }

    public function check(){
        $memory =  memory_get_usage();
//        $cpuInfo = getrusage();

//        echo "###########################\n";
//        echo 'time:'.time()."\n";
//        echo "check:workerId:{$this->workerId},memory:{$memory}\n";
//        echo "\n\n\n\n\n\n\n";

        $memory_limit = isset($this->config['memory_limit'])?$this->config['memory_limit']:0;
        if($memory_limit && $memory > $memory_limit){
            $this->closeWorker();
        }
    }


    public function closeWorker()
    {
//        echo "close:workerId:{$this->workerId}\n";

        /* @var $this->server Server */
        $this->server->swooleServer->stop();
    }

}