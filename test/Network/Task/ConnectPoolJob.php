<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/3/15
 * Time: 15:37
 */

namespace Zan\Framework\Test\Network\Task;


use Zan\Framework\Network\Common\ConnectionManager;
use Zan\Framework\Test\Foundation\Coroutine\Task\Job;

class ConnectPoolJob extends Job{

    public function run()
    {
        $m = new ConnectionManager(null);

        $pools = (yield $m::get('p_zan'));

        swoole_event_exit();


        yield 'conn is ok';
    }

}