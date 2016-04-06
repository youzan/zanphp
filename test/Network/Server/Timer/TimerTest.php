<?php
namespace Zan\Framework\Test\Network\Server\Timer;

use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Server\Timer\TimerJob;
use Zan\Framework\Network\Server\Timer\TickTimerManager;
use Zan\Framework\Network\Server\Timer\AfterTimerManager;

use Zan\Framework\Utilities\DesignPattern\Context;

class TimerTest extends \TestCase
{
    public function testTimerTickCreate()
    {
        $context = new Context();

        $context->set('begin_time', time());
        $context->set('interval', 1000);
        $context->set('tick_name', '侑子姐姐赛高哒');

        $testCase = $this;
        Timer::tick($context->get('interval'), $context->get('tick_name'), function($jobName) use ($context, $testCase) {
            var_dump($jobName);
        });

        $timers = TickTimerManager::getInstance()->show();

        var_dump($timers);

        Timer::clearTickJob($context->get('tick_name'));

        $timers = TickTimerManager::getInstance()->show();

        var_dump($timers);
    }
//
//    public function testTimerTickClear()
//    {
//        $context = new Context();
//
//        $context->set('begin_time', time());
//        $context->set('interval', 3000);
//        $context->set('tick_name', '侑子姐姐赛高哒');
//
//        Timer::tick($context->get('interval'), $context->get('tick_name'), function($jobName) {});
//
//        Timer::clearTickJob($context->get('tick_name'));
//
//        $timer = TickTimerManager::get($context->get('tick_name'));
//
//        $this->assertFalse($timer);
//    }
//
    public function testTimerAfterCreate()
    {
        $context = new Context();

        $context->set('begin_time', time());
        $context->set('interval', 1000);
        $context->set('after_name', '绫濑小公主萌萌哒');

        $testCase = $this;
        Timer::after($context->get('interval'), $context->get('after_name'), function() use ($context, $testCase) {
            var_dump(AfterTimerManager::getInstance()->show());
        });

        var_dump(AfterTimerManager::getInstance()->show());
    }
//
//    public function testTimerAfterClear()
//    {
//        $context = new Context();
//
//        $context->set('begin_time', time());
//        $context->set('interval', 3000);
//        $context->set('after_name', '绫濑小公主萌萌哒');
//
//        Timer::tick($context->get('interval'), $context->get('after_name'), function() {});
//
//        Timer::clearAfterJob($context->get('after_name'));
//
//        $timer = AfterTimerManager::get($context->get('after_name'));
//
//        $this->assertFalse($timer);
//    }
//
//    public function testTimerManager()
//    {
//        $context = new Context();
//
//        $context->set('begin_time', time());
//        $context->set('interval', 5000);
//        $context->set('tick_name', '侑子姐姐赛高哒');
//        $context->set('after_name', '绫濑小公主萌萌哒');
//
//        Timer::tick($context->get('interval'), $context->get('tick_name'), function($jobName) {});
//        Timer::after($context->get('interval'), $context->get('after_name'), function() {});
//
//        $tickTimer = TickTimerManager::get($context->get('tick_name'));
//        $tickTimers = TickTimerManager::show();
//
//        $afterTimer = AfterTimerManager::get($context->get('after_name'));
//        $afterTimers = AfterTimerManager::show();
//
//        $this->assertTrue($tickTimer instanceof TimerJob, 'tick timer manager get is not instanceof TimerJob!');
//        $this->assertEquals(1, count($tickTimers), 'tick timer manager show list count error!');
//
//        $this->assertTrue($afterTimer instanceof TimerJob, 'tick timer manager get is not instanceof TimerJob!');
//        $this->assertEquals(1, count($afterTimers), 'tick timer manager show list count error!');
//    }
}