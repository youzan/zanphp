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

namespace Zan\Framework;

class Zan {

    public static function createHttpApplication($config)
    {
        return new \HttpApplication($config);
    }

    public static function createTcpApplication($config)
    {
        return new \TcpApplication($config);
    }

    public static function createSocketApplication()
    {

    }

    public static function init()
    {
        self::initClassAlias();
    }

    private static function initClassAlias()
    {
        if(!file_exists(__DIR__ . '/ClassAlias.php')){
            return null;
        }

        require __DIR__ . '/ClassAlias.php';

        if (isset($classAliasMap) && $classAliasMap ){
            self::initClassAliasMap($classAliasMap);
        }

        if (isset($classAliasPathes) && $classAliasPathes ){
            self::initClassAliasPathes($classAliasPathes);
        }
    }

    private static function initClassAliasMap($classAliasMap)
    {
        foreach($classAliasMap as $alias => $original) {
            class_alias($original, $alias);
        }
    }

    private static function initClassAliasPathes($classAliasPathes)
    {

    }
}
Zan::init();
