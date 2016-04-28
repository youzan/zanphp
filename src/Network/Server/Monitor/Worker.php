<?php

/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/4/26
 * Time: 下午4:57
 */

namespace Zan\Framework\Network\Server\Monitor;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use  Zan\Framework\Network\Http\Server;

use Zan\Framework\Network\Server\Timer\Timer;


class Worker
{
    use Singleton;

    const GAP_TIME = 180000;

    public $classHash;
    public $workerId;
    public $server;
    public $config;

    public $reactionNum;
    public $totalReactionNum;

    /* @var $server Server */
    public function init($server,$config){
        if(!is_array($config)){
            return ;
        }

        $this->classHash = spl_object_hash($this);
        $this->server = $server;
        $this->workerId = $server->swooleServer->worker_id;
        $this->config = $config;
        $this->reactionNum = 0;
        $this->totalReactionNum = 0;

        $this->restart();
        $this->checkStart();
    }

    public function restart()
    {
        $time = isset($this->config['live_time'])?$this->config['live_time']:1800000;
        $time += $this->workerId * self::GAP_TIME;

        Timer::after($time, $this->classHash.'_restart',[$this,'preCloseWorker']);
    }

    public function checkStart(){
        $time = isset($this->config['check_time'])?$this->config['check_time']:5000;

        Timer::tick($time, $this->classHash.'_check',[$this,'check']);
    }

    public function check(){
        $memory =  memory_get_usage();
//        $cpuInfo = getrusage();

//        echo "###########################\n";
//        echo 'time:'.time()."\n";
//        echo "check:workerId:{$this->workerId},memory:{$memory}\n";
//        echo "request number:".$this->reactionNum."\n";
//        echo "total request number:".$this->totalReactionNum."\n";
//        echo "\n\n\n\n\n\n\n";

        $memory_limit = isset($this->config['memory_limit'])
                ? $this->config['memory_limit']
                : 1024 * 1024 * 1024 * 1.5;

        if($memory > $memory_limit){
            $this->preCloseWorker();
        }
    }


    public function preCloseWorker()
    {
//        echo "close:workerId:{$this->workerId}\n";

        Timer::clearTickJob($this->classHash.'_check');

        /* @var $this->server Server */
        $this->server->swooleServer->deny_request($this->workerId);
        $this->closeWorker();
    }

    public function closeWorker(){
        if($this->reactionNum >= 0){
            Timer::after(500,$this->classHash.'close',[$this,'closeWorker']);
        }
        $this->server->swooleServer->exit();
    }


    public function reactionReceive(){
        $this->totalReactionNum++;
        $this->reactionNum ++;
    }

    public function reactionRelease(){
        $this->reactionNum --;
    }

}