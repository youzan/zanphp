<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/7
 * Time: 20:04
 */
namespace Zan\Framework\Test\Sdk\Timer;

use Zan\Framework\Sdk\Timer\Timer;

class TimerTest extends \TestCase
{
    public function testTimerTick()
    {

        var_dump(time());
        Timer::tick(2000, function($timerHash, $params){
            var_dump(time());
            var_dump($timerHash, $params);
        }, 'wahaha');

    }
}