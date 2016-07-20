<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/28
 * Time: 23:16
 */

namespace Zan\Framework\Test\Foundation\Coroutine\Task;


use Zan\Framework\Foundation\Contract\Async;

class AsyncTest implements  Async
{
    private $callback = null;

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    public function fakeResponse() {
        $response = new Response(200,'ok',['data'=>'rpc']);
        call_user_func($this->callback, $response);
    }
}