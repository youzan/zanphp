<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/28
 * Time: 23:16
 */

namespace Zan\Framework\Test\Foundation\Coroutine\Task;

class Coroutine extends Job {
    public function run() {
        $work = (yield $this->work());
        $this->context->set('work_response', $work);

        yield 'coroutine job done';
    }

    private function work() {
        $step1 = (yield $this->step1());
        $step2 = (yield $this->step2());

        $this->context->set('step1_response', $step1);
        $this->context->set('step2_response', $step2);

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
}