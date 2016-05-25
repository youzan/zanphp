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
$classAliasMap = [
    'Zan'             => 'Zan\\Framework\\Zan',
    'UnitTest'        => 'PHPUnit_Framework_TestCase',
    'Config'          => 'Zan\\Framework\\Foundation\\Core\\Config',
    'Handler'         => 'Zan\\Framework\\Foundation\\Exception\\Handler',
    'HttpServer'      => 'Zan\\Framework\\Network\\Http\\Server',
    'HttpApplication' => 'Zan\\Framework\\Network\\Http\\Application',
    'TcpServer'       => 'Zan\\Framework\\Network\\Tcp\\Server',
    'TcpApplication'  => 'Zan\\Framework\\Network\\Tcp\\Application',
];

$classAliasPathes = [
    'Foundation/Contract',
    'Foundation/Core',
    'Foundation/Domain',
    'Utilities/Types',
];