<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/7
 * Time: 20:53
 */

namespace Zan\Framework\Test\Foundation\Coroutine\SysCall;

use Zan\Framework\Test\Foundation\Coroutine\Task\Job;

class KillTask extends Job{
    public function run() {
        $return = (yield 'SysCall.KillTask.calling');

        $this->context->set('step1', 'before task killed');

        yield killTask();

        $this->context->set('step2', 'after task killed');

        yield 'SysCall.KillTask';
    }
}