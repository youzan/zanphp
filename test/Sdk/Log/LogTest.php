<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/4/10
 * Time: 下午1:00
 */
namespace Zan\Framework\Test\Sdk\Log;

use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Testing\TaskTest;
use Zan\Framework\Sdk\Log\Log;

class LogTest extends TaskTest
{
    public function initTask()
    {
        //connection pool init
//        ConnectionInitiator::getInstance()->init('connection', null);
        parent::initTask();
    }

    public function taskSysLog()
    {
        yield Log::make("default")->info('log to syslog');
    }

    public function taskLogLog()
    {
        // log file position: resource/log/log.txt
        yield Log::make("log_log")->info('log to file');
    }
}