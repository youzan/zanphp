<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/28
 * Time: 23:16
 */

namespace Zan\Framework\Test\Foundation\Coroutine\Task;

class YieldValues extends Job {
    private $rpc = null;
    public function run() {
        $work = (yield $this->work());
        $this->context->set('work_response', $work);

        yield 'YieldValues job done';
    }

    private function work() {
        $step1 = (yield $this->step1());
        $step2 = (yield $this->step2());
        $step3 = (yield $this->step3());
        $step4 = (yield $this->step4());

        $this->context->set('step1_response', $step1);
        $this->context->set('step2_response', $step2);
        $this->context->set('step3_response', $step3);
        $this->context->set('step4_response', $step4);

        yield 'coroutine.work()';
    }

    private function step1()  {
        $this->context->set('step1_call', 'step1');
        yield 'coroutine.step1()';
    }

    private function step2()  {
        $inner = (yield $this->inner());

        $this->context->set('step2_call', 'step2');
        $this->context->set('step2_inner', $inner);

        yield 'coroutine.step2()';
    }

    private function inner() {
        $this->context->set('inner_call', 'inner');
        yield 'coroutine.inner()';
    }

    private function step3() {
        $response = (yield $this->call());

        $this->context->set('response', $response);

        yield $response;
    }

    public function fakeResponse() {
        return $this->rpc->fakeResponse();
    }

    private function step4()  {
        $this->context->set('step4_call', 'step4');
        yield 'coroutine.step4()';
        yield 'coroutine.step44()';
        yield 'coroutine.step444()';
        yield 'coroutine.step44444444()';
    }

    private function call() {
        $this->context->set('call()','call');
        $this->rpc = new AsyncTest();

        yield $this->rpc;
    }
}