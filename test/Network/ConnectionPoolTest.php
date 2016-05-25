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

namespace Zan\Framework\Test\Network;



use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Testing\TaskTest;


class ConnectionPoolTest extends TaskTest {


    public function taskPoolWork()
    {
        ConnectionInitiator::getInstance()->init([], null);


        $pool = (yield ConnectionManager::getInstance()->get('pifa'));
        $pool->close();

        for ($i=0; $i<5;$i++) {
        $pool = (yield ConnectionManager::getInstance()->get('pifa'));

        var_dump($pool->getSocket());
        }

    }

}