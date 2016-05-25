<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
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