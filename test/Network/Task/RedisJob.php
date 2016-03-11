<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/3/11
 * Time: 14:13
 */

namespace Zan\Framework\Test\Network\Task;

use Zan\Framework\Network\Common\RedisManager;
use Zan\Framework\Test\Foundation\Coroutine\Task\Job;

class RedisJob extends Job {
    public function run()
    {
        $redis = new RedisManager('127.0.0.1');

        $setRet = (yield $redis->set('abc', 'retValue'));
        $getRet = (yield $redis->get('abc'));

        $this->context->set('getRet', $getRet);
    }
}