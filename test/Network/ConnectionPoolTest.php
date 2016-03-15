<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:20
 */

namespace Zan\Framework\Test\Network;



use Zan\Framework\Foundation\Test\UnitTest;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Foundation\Coroutine\Context;
use Zan\Framework\Test\Network\Task\ConnectPoolJob;

require __DIR__ . '/../../' . 'src/Test.php';

class ConnectionPoolTest extends UnitTest {


    public function testPoolWork()
    {
//        $m = new ConnectionManager(null);
//
//        $pools = (yield $m::get('p_zan'));
//
//        var_dump($pools);
//        exit;

        $context = new Context();

        $job = new ConnectPoolJob($context);
        $coroutine = $job->run();


        $task = new Task($coroutine);
        $task->run();


        $result = $context->show();
        var_dump($result);exit;
    }

}