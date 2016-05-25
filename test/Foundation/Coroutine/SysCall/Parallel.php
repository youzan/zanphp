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
namespace Zan\Framework\Test\Foundation\Coroutine\SysCall;

use Zan\Framework\Test\Foundation\Coroutine\Task\Job;

class Parallel extends Job
{
    public function run()
    {
        $firstValue = $this->context->get('first_coroutine');
        $secondValue = $this->context->get('second_coroutine');
        $thirdValue = $this->context->get('third_coroutine');

        $coroutines = [
            $this->firstCoroutine($firstValue),
            $this->secondCoroutine($secondValue),
            $this->getFunctionResult($thirdValue),
            $this->sysCall()
        ];

        $value = (yield parallel($coroutines));
        $this->context->set('parallel_value', $value);
        yield 'SysCall.Parallel';
    }


    private function firstCoroutine($value)
    {
        yield $value;
    }

    private function secondCoroutine($value)
    {
        yield $value;
    }

    private function getFunctionResult($thirdValue)
    {
        return $thirdValue;
    }

    private function sysCall()
    {
        yield getTaskId();
    }
}