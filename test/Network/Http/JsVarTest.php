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

namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Http\JsVar;

class JsVarTest extends \TestCase
{
    private $_jsVar = null;

    public function setUp()
    {
        $this->_jsVar = new JsVar();
        $this->_jsVar->setSession('kdt_id', 1);
        $this->_jsVar->setConfig('run_mode', 'online');
        $this->_jsVar->setQuery('query_path', 'showcase/goods/index');
        $this->_jsVar->setEnv('platform', 'ios');
    }

    public function tearDown()
    {
        $this->_jsVar = null;
    }

    public function testGet(){
        $excepted = [
            'session' => ['kdt_id' => 1],
            'query' => ['query_path' => 'showcase/goods/index'],
            'config' => ['run_mode' => 'online'],
            'env' => ['platform' => 'ios'],
        ];
        $excepted = json_encode($excepted);
        $jsVarData = $this->_jsVar->get();
        $jsVarData = json_encode($jsVarData);
        $this->assertEquals($excepted, $jsVarData, 'JsVarTest::getData fail');
    }
} 