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
        $this->assertEquals('simple job done', $taskData, 'get task final output fail');
    }

    public function testSysCallWorkFine() {

    }

    public function testAsyncWorkFine() {

    }

    public function testExceptionWorkFine() {

    }
}