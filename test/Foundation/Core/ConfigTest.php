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

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Foundation\Core\Path;


class ConfigTest extends \TestCase
{
    public function setUp()
    {
        $path = __DIR__ . '/config/';
        Path::setConfigPath($path);
    }

    public function tearDown()
    {
        Config::clear();
    }


    public function testGetConfigWork()
    {
        RunMode::set('online');
        Config::init();
        $data = Config::get('a.share');
        $this->assertEquals('share', $data, 'Config::get share get failed');
        $data = Config::get('a.config');
        $this->assertEquals('online', $data, 'Config::get share get failed');
        $data = Config::get('pf.b.test');
        $this->assertEquals('online', $data, 'Config::get share get failed');
        $data = Config::get('pf.b.db');
        $this->assertEquals('pf', $data, 'Config::get share get failed');
        Config::set('pf.b.new','new');
        $data = Config::get('pf.b.new');
        $this->assertEquals('new', $data, 'Config::set failed');
        Config::set('pf','delete');
        $data = Config::get('pf');
        $this->assertEquals('delete', $data, 'Config::set failed');
    }

}