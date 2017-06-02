<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/4
 * Time: 13:43
 */

namespace Zan\Framework\Test\Foundation\Coroutine\Task;


class Steps extends Job {
    public function run()
    {
        //echo "\n\n\n";
        $result = (yield $this->step1());

        $this->context->set('result', $result);

        yield $result;
    }

    public function step1()
    {
        $ret = (yield $this->step2());
        yield $ret;
        //echo 'step1:', $ret . "\n";
    }

    public function step2()
    {
        $ret = (yield $this->step3());
        yield $ret;
        //echo 'step2:', $ret . "\n";
    }

    public function step3()
    {
        $ret = (yield $this->step4());
        yield $ret;
        //echo 'step3:', $ret . "\n";
    }

    public function step4()
    {
        yield 'step4';
        yield 'stepN';
    }
}