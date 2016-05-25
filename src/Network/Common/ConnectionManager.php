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

namespace Zan\Framework\Network\Facade;

use Zan\Framework\Network\Contract\ConnectionPool as Pool;


class ConnectionManager {
    private static $poolMap = [];

    public static function init()
    {
        self::$poolMap = [];
    }

    public static function addPool($key, Pool $pool)
    {
        self::$poolMap[$key] = $pool;
    }

    public static function get($key) /* Connection */
    {
        if(!isset(self::$poolMap[$key])){
            return null;
        }
        $pool = self::$poolMap[$key];
        $conn = (yield $pool->get());

        defer(function() use($conn, $key){
            ConnectionManager::release($conn, $key);
        });
    }

    public static function release($key=null,Connection $conn)
    {

    }

}


