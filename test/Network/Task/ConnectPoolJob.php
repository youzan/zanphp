<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/3/15
 * Time: 15:37
 */

namespace Zan\Framework\Test\Network\Task;


use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Test\Foundation\Coroutine\Task\Job;

class ConnectPoolJob extends Job{

    public function run()
    {
        $m = new ConnectionInitiator();
        $m->init([]);
        $cm = new ConnectionManager();

        $pools = (yield $cm->get('pifa'));

        swoole_event_exit();


        yield 'conn is ok';
    }

}