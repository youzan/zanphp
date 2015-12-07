<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/7
 * Time: 20:03
 */

namespace Zan\Framework\Test\Foundation\Coroutine\SysCall;

use Zan\Framework\Test\Foundation\Coroutine\Task\Job;

class GetTaskId extends Job
{
    public function run()
    {
        $value = (yield getTaskId());

        $this->context->set('taskId', $value);

        yield 'SysCall.GetTastId';
    }
}