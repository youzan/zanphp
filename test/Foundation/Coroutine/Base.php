<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 14:44
 */

namespace Zan\Framework\Test\Foundation\Coroutine;


use Zan\Framework\Foundation\Coroutine\Task;

class Base extends \TestCase{
    public function testTaskWork()
    {
        $coroutine = $this->step();

        Task::execute($coroutine);
    }
}