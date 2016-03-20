<?php
/**
 *  @see zan/test/Foundation/Coroutine/TaskDemoTest.php
 */
namespace Zan\Framework\Testing;;

use Zan\Framework\Foundation\Coroutine\Task;

class TaskTest extends UnitTest {
    protected $taskMethodPattern = '/^task.+/i';

    public function testTasksWork()
    {
        $ref = new \ReflectionClass($this);
        $methods = $ref->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method) {
            $methodName = $method->getName();
            if(!preg_match($this->taskMethodPattern, $methodName)){
                continue;
            }

            $coroutine = $this->$methodName();
            Task::create($coroutine);
        }
    }


}





