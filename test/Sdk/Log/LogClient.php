<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 16/3/15
 * Time: 14:14
 */

namespace Zan\Framework\Test\Sdk\Log;

use Zan\Framework\Sdk\Log\Logger;
use Zan\Framework\Testing\TaskTest;
use Zan\Framework\Foundation\Core\Path;

class LogClient extends TaskTest {

    public function taskWirteLog(){
        $path = __DIR__ . '/log/';
        Path::setLogPath($path);
        $log = Logger::getInstance('trade');

        $data = ['name'=>'hht', 'context'=>'test'];
        $ret = (yield $log->info($data));
        var_dump($ret);
    }
}
