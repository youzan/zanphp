<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/28
 * Time: 23:16
 */

namespace Zan\Framework\Test\Foundation\Coroutine\Task;


class AsyncJob extends Job {
    private $rpc = null;
    public function run() {
        $response = (yield $this->call());

        $this->context->set('response', $response);

        yield $response;
    }

    public function fakeResponse() {
        return $this->rpc->fakeResponse();
    }

    private function call() {
        $this->context->set('call()','call');
        $this->rpc = new AsyncTest();

        yield $this->rpc;
    }
}