<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:20
 */

namespace Zan\Framework\Test\Network;



use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Common\ConnectionManager;
use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Test\Foundation\Coroutine\Context;
use Zan\Framework\Test\Network\Task\ConnectPoolJob;


class ConnectionPoolTest extends \TestCase {


    public function testPoolWork()
    {
//        $m = new ConnectionInitiator();
//        $m->init(null);
//        $cm = new ConnectionManager();

//        $pools = (yield $cm::get('pifa'));
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