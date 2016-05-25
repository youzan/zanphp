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

namespace Zan\Framework\Utilities\Types;


use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class Number {
    public static function floatToString($float) /* string */
    {
        if(is_string($float)) {
            return $float;
        }

        if(!is_float($float)) {
            throw new InvalidArgumentException('invalid argument for Number::floatToString(' . $float . ')');
        }

        $string = (string) $float;
        $string = str_replace('.', '', $string);
        $string = ltrim($string, '0');

        return $string;
    }
}