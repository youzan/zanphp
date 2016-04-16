<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:20
 */

namespace Zan\Framework\Test\Network;



use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Test\Foundation\Coroutine\Context;
use Zan\Framework\Test\Network\Task\ConnectPoolJob;
use Zan\Framework\Testing\TaskTest;


class ConnectionPoolTest extends TaskTest {


    public function taskPoolWork()
    {
        $m = new ConnectionInitiator();
        $m->init([]);
        $cm = new ConnectionManager();


        $pool = (yield $cm->get('pifa'));
        $pool->close();

        for ($i=0; $i<5;$i++) {
        $pool = (yield $cm->get('pifa'));

        var_dump($pool->getSocket());
        }

    }

}