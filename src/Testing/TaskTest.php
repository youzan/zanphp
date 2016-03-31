<?php
/**
 * @see zan/test/Foundation/Coroutine/TaskDemoTest.php
 */
namespace Zan\Framework\Testing;

use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Foundation\Core\EventChain;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Foundation\Coroutine\Task\parallel;

class TaskTest extends UnitTest
{
    protected $taskMethodPattern = '/^task.+/i';

    protected $isInitialized = false;
    protected $counter = 0;
    protected $coroutines = [];

    public function init()
    {
        if ($this->isInitialized) {
            return false;
        }
        Event::bind('test_task_done', function () {
            swoole_event_exit();
        });
        $this->isInitialized = true;
    }

    public function testTasksWork()
    {
        $this->counter++;
        EventChain::before('test_task_num_' . $this->counter, 'test_task_done');

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

    public function runTest()
    {
        yield parallel($this->coroutines);
        Event::fire('test_task_done');
    }

}





