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
        $log = LoggerFactory::getInstance('trade');
        $ret = (yield $log->info('test'));
        var_dump($ret);
    }
}
