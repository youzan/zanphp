<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/7
 * Time: 20:04
 */
namespace Zan\Framework\Test\Foundation\Coroutine;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Foundation\Coroutine\SysCall\GetTaskId;
use Zan\Framework\Test\Foundation\Coroutine\SysCall\KillTask;
use Zan\Framework\Test\Foundation\Coroutine\SysCall\Parallel;

class SysCallTest extends \TestCase
{
    public function testGetTaskId()
    {
        $context = new Context();

        $job = new GetTaskId($context);
        $coroutine = $job->run();

        $task = new Task($coroutine, null, 8);
        $task->run();

        $result = $context->show();
        $this->assertArrayHasKey('taskId',$result, 'GetTaskId job failed to set context');
        $this->assertEquals(8, $context->get('taskId'), 'GetTaskId job get wrong context value');

        $taskData = $task->getResult();
        $this->assertEquals('SysCall.GetTastId', $taskData, 'get GetTaskId task final output fail');
    }

    public function testKillTask()
    {
        $context = new Context();

        $job = new KillTask($context);
        $coroutine = $job->run();

        $task = new Task($coroutine, null, 8);
        $task->run();

        $result = $context->show();
        $this->assertArrayHasKey('step1',$result, 'KillTask job failed to set context');
        $this->assertArrayNotHasKey('step2',$result, 'KillTask job failed to set context');
        $this->assertEquals('before task killed', $context->get('step1'), 'KillTask job get wrong context value');

        $taskData = $task->getResult();
        $this->assertEquals('SysCall.KillTask.calling', $taskData, 'get KillTask task final output fail');
    }

    public function testParallel()
    {
        $context = new Context();

        $context->set('first_coroutine', 'Hello');
        $context->set('second_coroutine', 'World');
        $context->set('third_coroutine', 'coroutine');

        $job = new Parallel($context);
        $coroutine = $job->run();

        $task = new Task($coroutine, null, 19);
        $task->run();

        $result = $context->show();

        $this->assertArrayHasKey('parallel_value',$result, 'parallel job failed to set context');
        $this->assertEquals(4, count($result['parallel_value']), 'parallel result number get wrong');
        $this->assertEquals($context->get('first_coroutine'), $result['parallel_value'][0], 'parallel callback 1 get wrong context value');
        $this->assertEquals($context->get('second_coroutine'), $result['parallel_value'][1], 'parallel callback 2 get wrong context value');
        $this->assertEquals($context->get('third_coroutine'), $result['parallel_value'][2], 'parallel callback 3 get wrong context value');
        $this->assertInternalType('int', $result['parallel_value'][3], 'parallel callback 4 get wrong context value');

        $taskData = $task->getResult();
        $this->assertEquals('SysCall.Parallel', $taskData, 'get Parallel task final output fail');
    }

    public function tearDown()
    {
        //swoole_event_exit();
    }
}