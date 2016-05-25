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
namespace Zan\Framework\Utilities\DesignPattern;


class Registry  {
    private static $classMap = [];

    public static function get($key, $default=null)
    {
        if(!isset(self::$classMap[$key])) {
            return $default;
        }

        return self::$classMap[$key];
    }

    public static function set($key, $value)
    {
        self::$classMap[$key] = $value;
    }

    public static function contain($key)
    {
        return isset(self::$classMap[$key]);
    }
}