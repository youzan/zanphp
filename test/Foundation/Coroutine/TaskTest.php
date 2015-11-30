<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/10/20
 * Time: 21:35
 */
namespace Zan\Framework\Test\Foundation\Coroutine;

require __DIR__ . '/../../../' . 'src/Zan.php';

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Foundation\Coroutine\Task\Coroutine;
use Zan\Framework\Test\Foundation\Coroutine\Task\Error;
use Zan\Framework\Test\Foundation\Coroutine\Task\Simple;


class TaskTest extends \UnitTest {
    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testSimpleYieldWorkFine() {
        $context = new Context();

        $job = new Simple($context);
        $coroutine = $job->run();

        $task = new Task($coroutine);
        $task->run();

        $result = $context->show();
        $this->assertArrayHasKey('key',$result, 'simple job failed to set context');
        $this->assertEquals('simple value', $context->get('key'), 'simple job get wrong context value');

        $taskData = $task->getSendValue();
        $this->assertEquals('simple job done', $taskData, 'get simple task final output fail');
    }

    public function testCoroutineWorkFine() {
        $context = new Context();

        $job = new Coroutine($context);
        $coroutine = $job->run();

        $task = new Task($coroutine);
        $task->run();

        $result = $context->show();

        $this->assertArrayHasKey('step1_call',$result, 'coroutine job failed to set context');
        $this->assertEquals('step1', $context->get('step1_call'), 'coroutine job get wrong context value');

        $this->assertArrayHasKey('step2_call',$result, 'coroutine job failed to set context');
        $this->assertEquals('step2', $context->get('step2_call'), 'coroutine job get wrong context value');

        $this->assertArrayHasKey('inner_call',$result, 'coroutine job failed to set context');
        $this->assertEquals('inner', $context->get('inner_call'), 'coroutine job get wrong context value');

        $this->assertArrayHasKey('step2_inner',$result, 'coroutine job failed to set context');
        $this->assertEquals('coroutine.inner()', $context->get('step2_inner'), 'coroutine job get wrong context value');

        $this->assertArrayHasKey('step1_response',$result, 'coroutine job failed to set context');
        $this->assertEquals('coroutine.step1()', $context->get('step1_response'), 'coroutine job get wrong context value');

        $this->assertArrayHasKey('step2_response',$result, 'coroutine job failed to set context');
        $this->assertEquals('coroutine.step2()', $context->get('step2_response'), 'coroutine job get wrong context value');

        $this->assertArrayHasKey('work_response',$result, 'coroutine job failed to set context');
        $this->assertEquals('coroutine.work()', $context->get('work_response'), 'coroutine job get wrong context value');

        $taskData = $task->getSendValue();
        $this->assertEquals('coroutine job done', $taskData, 'get coroutine task final output fail');
    }

    public function testSysCallWorkFine() {

    }

    public function testAsyncWorkFine() {

    }

    public function testExceptionWorkFine() {
        $context = new Context();

        $job = new Error($context);
        $coroutine = $job->run();

        $task = new Task($coroutine);
        $task->run();

        $result = $context->show();

        $this->assertArrayHasKey('step1_response',$result, 'exception job failed to set context');
        $this->assertEquals('step1', $context->get('step1_response'), 'exception job get wrong context value');

        $this->assertArrayHasKey('exception_code',$result, 'exception job failed to set context');
        $this->assertEquals(404, $context->get('exception_code'), 'exception job get wrong context value');

        $this->assertArrayHasKey('exception_msg',$result, 'exception job failed to set context');
        $this->assertEquals('ErrorException Msg', $context->get('exception_msg'), 'exception job get wrong context value');

        $this->assertArrayHasKey('exception',$result, 'exception job failed to set context');
        $this->assertEquals('Zan\Framework\Test\Foundation\Coroutine\Task\ErrorException', $context->get('exception'), 'exception job get wrong context value');

        $this->assertArrayNotHasKey('work_response',$result, 'exception job failed to set context');

        $taskData = $task->getSendValue();
        $this->assertEquals('step1', $taskData, 'get exception task final output fail');
    }
}