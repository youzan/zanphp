<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:20
 */


namespace Zan\Framework\Test\Store\Database;
require __DIR__ . '/../../../' . 'src/Test.php';

use Zan\Framework\Foundation\Test\UnitTest;

use Zan\Framework\Test\Foundation\Coroutine\Context;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Store\Database\Sql\Task\InsertJob;
use Zan\Framework\Test\Store\Database\Sql\Task\QueryJob;

class MysqlTest extends UnitTest {

    public function testInsert()
    {
        $context = new Context();
        $job = new InsertJob($context);
        $coroutine = $job->run();

        $task = new Task($coroutine);
        $task->run();
        $result = $context->show();
        print_r($result);exit;
    }
}