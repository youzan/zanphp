<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/7
 * Time: 20:04
 */
namespace Zan\Framework\Test\Foundation\Coroutine;
require __DIR__ . '/../../../' . 'src/Zan.php';

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Foundation\Coroutine\SysCall\GetTaskId;
use Zan\Framework\Test\Foundation\Coroutine\SysCall\KillTask;

class SysCallTest extends \UnitTest
{
    public function testGetTaskId()
    {
        $context = new Context();

        $job = new GetTaskId($context);
        $coroutine = $job->run();

        $task = new Task($coroutine, 8);
        $task->run();

        $result = $context->show();
        $this->assertArrayHasKey('taskId',$result, 'GetTaskId job failed to set context');
        $this->assertEquals(8, $context->get('taskId'), 'GetTaskId job get wrong context value');

        $taskData = $task->getSendValue();
        $this->assertEquals('SysCall.GetTastId', $taskData, 'get GetTaskId task final output fail');
    }

    public function testKillTask()
    {
        $context = new Context();

        $job = new KillTask($context);
        $coroutine = $job->run();

        $task = new Task($coroutine, 8);
        $task->run();

        $result = $context->show();
        $this->assertArrayHasKey('step1',$result, 'KillTask job failed to set context');
        $this->assertArrayNotHasKey('step2',$result, 'KillTask job failed to set context');
        $this->assertEquals('before task killed', $context->get('step1'), 'KillTask job get wrong context value');

        $taskData = $task->getSendValue();
        $this->assertEquals('SysCall.KillTask.calling', $taskData, 'get KillTask task final output fail');
    }
}