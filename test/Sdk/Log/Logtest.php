<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 16/3/14
 * Time: 14:18
 */
namespace Zan\Framework\Test\Sdk\Log;

use Zan\Framework\Sdk\Log\LoggerFactory;
use Zan\Framework\Foundation\Coroutine\Task;

class Logtest extends \PHPUnit_Framework_TestCase{
    public function testNew(){

        $log        = LoggerFactory::getLogger('zanhttdemo');
        $task       = $log->info('hht test');
        print_r($task);exit;
        $scheduler  = new Task($task);
        $scheduler->run();
    }
}
