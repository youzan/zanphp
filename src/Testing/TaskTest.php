<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
namespace Zan\Framework\Testing;

use Zan\Framework\Foundation\Coroutine\Event;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\Types\Time;

class TaskTest extends UnitTest
{
    public static $isInitialized = false;
    public static $event = null;
    public static $eventChain = null;
    
    protected $taskMethodPattern = '/^task.+/i';
    protected $taskCounter = 0;
    protected $coroutines = [];

    public function testTasksWork()
    {
        $this->initTask();

        $this->taskCounter++;
        TaskTest::$eventChain->before('test_task_num_' . $this->taskCounter, 'test_task_done');

        $this->scanTasks(); 
        $taskCoroutine = $this->runTaskTests();
        $context = new Context();
        $context->set('request_time', Time::stamp());
        $request_timeout = 30;
        $context->set('request_timeout', $request_timeout);
        Task::execute($taskCoroutine, $context);
    }
    
    protected function scanTasks()
    {
        $ref = new \ReflectionClass($this);
        $methods = $ref->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();
            if (!preg_match($this->taskMethodPattern, $methodName)) {
                continue;
            }

            $coroutine = $this->$methodName();
            $this->coroutines[] = $coroutine;
        } 
    }

    protected function initTask()
    {
        if (TaskTest::$isInitialized) {
            return false;
        }
        TaskTest::$isInitialized = true;
        
        TaskTest::$event = new Event();
        TaskTest::$eventChain = TaskTest::$event->getEventChain();
        
        TaskTest::$event->bind('test_task_done', function () {
            swoole_event_exit();
        });
    }
    
    protected function runTaskTests()
    {
        yield parallel($this->coroutines);
        TaskTest::$event->fire('test_task_done');
    }

}





