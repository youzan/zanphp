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
return [
    'server' => [
        'host'          => '127.0.0.1',
        'port'          => '5601',
        'worker_num'    => 4,
        'max_request'   => 5000,
    ],

    'client' => [
        'host'          => 'xxx.com',
        'port'          => '80',
        'timeout'       => 1,
    ],


];