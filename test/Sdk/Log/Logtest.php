<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 16/3/14
 * Time: 14:18
 */
namespace Zan\Framework\Test\Sdk\Log;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Sdk\Log\LoggerFactory;

class Logtest extends \PHPUnit_Framework_TestCase {
    public function testWirteLog(){
        /*
        $test = new LogClient();
        $result = $test->addLog('hht test');
        $task = new Task($result);
        $task->run();
        */
        LoggerFactory::getInstance('trade')->info('test');
    }
}
