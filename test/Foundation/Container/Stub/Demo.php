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

namespace Zan\Framework\Test\Foundation\Container\Stub;


class Demo
{
    private $arg0;
    private $arg1;

    public function __construct($arg0, $arg1)
    {

        $this->arg0 = $arg0;
        $this->arg1 = $arg1;
    }

    /**
     * @return mixed
     */
    public function getArg0()
    {
        return $this->arg0;
    }

    /**
     * @return mixed
     */
    public function getArg1()
    {
        return $this->arg1;
    }
    
}