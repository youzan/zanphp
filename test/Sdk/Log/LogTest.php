<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/4/10
 * Time: 下午1:00
 */
namespace Zan\Framework\Test\Sdk\Log;

use Zan\Framework\Testing\TaskTest;
use Zan\Framework\Sdk\Log\Log;

class LogTest extends TaskTest
{
    public function taskSysLog()
    {
        try {
            yield Log::make("default")->info('log to syslog');
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

    }

    public function taskLogLog()
    {
        // log file position: resource/log/log.txt
        yield Log::make("log_log")->info('log to file');
    }
}