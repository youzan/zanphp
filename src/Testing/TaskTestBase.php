<?php
/**
 *  @see zan/test/Foundation/Coroutine/TaskDemoTest.php
 */
namespace Zan\Framework\Testing;;

use Zan\Framework\Foundation\Coroutine\Task;

abstract class TaskTestBase extends \TestCase {

    private $testAction = [];

    abstract function taskStep();

    public function addTestAction($actions = [])
    {
        $this->testAction = $actions;
    }

    public function testTaskWork()
    {
        Task::create($this->taskStep());

        if (!$this->testAction) return;

        foreach ($this->testAction as $action) {
            Task::create($this->$action());
        }
    }

}



