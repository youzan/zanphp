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

namespace Zan\Framework\Network\Client;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Utilities\Types\Dir;

class ConnectionConfig {
    private static $configPath = '';
    private static $data = [];
    public static function setConfigPath($path)
    {
        if(!$path || !is_dir($path)) {
            throw new InvalidArgument('invalid path for ConnectionConfig ' . $path);
        }
        $path = Dir::formatPath($path);
        self::$configPath = $path;
    }

    public static function get($key)
    {
    }

    public static function clear()
    {
        self::$data = [];
    }

    private static function getConfigFile()
    {

    }
}