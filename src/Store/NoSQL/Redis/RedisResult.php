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

namespace Zan\Framework\Store\NoSQL\Redis;


use Zan\Framework\Foundation\Contract\Async;

class RedisResult implements  Async{

    private $callback = null;

    public function execute(callable $callback){
        $this->callback = $callback;
    }

    public function response($data, $status)
    {
        call_user_func($this->callback, $data);
    }
}