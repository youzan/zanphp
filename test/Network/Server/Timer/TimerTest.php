<?php
namespace Zan\Framework\Test\Network\Server\Timer;

use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Testing\TaskTest;

class TimerTest extends TaskTest
{
    public function taskAfterWork()
    {
        Timer::after(10, function(){
            var_dump(func_get_args());
        });
    }
}