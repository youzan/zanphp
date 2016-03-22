<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 16/3/14
 * Time: 14:18
 */
namespace Zan\Framework\Test\Sdk\Log;

use Zan\Framework\Sdk\Log\LoggerFactory;
use Zan\Framework\Testing\TaskTest;

class Logtest extends TaskTest {

    public function taskWirteLog(){

        $ret = (yield LoggerFactory::getInstance('trade')->info('test'));

        var_dump($ret);
        /*
        $test = new LogClient();
        $result = $test->addLog('hht test');
        $task = new Task($result);
        $task->run();
        */
    }
}
