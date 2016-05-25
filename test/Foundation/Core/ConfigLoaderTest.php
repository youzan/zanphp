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
namespace Zan\Framework\Test\Foundation\Core;

use Zan\Framework\Foundation\Core\ConfigLoader;


class ConfigLoaderTest extends \TestCase {

    private $path;

    public function setUp()
    {
        $this->path = __DIR__ . '/config/online';
    }

    public function tearDown()
    {
    }

    public function test(){
        $config = ConfigLoader::getInstance();
        $result = $config->load($this->path);
        $this->assertEquals('online', $result['a']['config'], 'ConfigLoader::load fail');
        $this->assertEquals('pf', $result['pf']['b']['db'], 'ConfigLoader::load fail');
        $this->assertEquals('pf', $result['pf']['a']['a'], 'ConfigLoader::load fail');
        $this->assertEquals('online', $result['pf']['b']['test'], 'ConfigLoader::load fail');
    }




}