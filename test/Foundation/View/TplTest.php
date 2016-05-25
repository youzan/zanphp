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

namespace Zan\Framework\Test\Foundation\View;

use Zan\Framework\Foundation\View\Tpl;

class TplTest extends \TestCase
{
    public $tplLoader = null;

    public function setUp()
    {
        $this->tplLoader = new TplLoader();
    }

    public function tearDown()
    {
        $this->tplLoader = null;
    }

    public function testLoad()
    {
        ob_start();
        $this->tplLoader->load(__DIR__ . '/Tpl/testTpl.html', ['a' => 1, 'b' => 2]);
        $content = ob_get_clean();

        $contentExcepted = 'content';
        $this->assertEquals($contentExcepted, $content, 'LayoutTest::testLoad fail');
    }
} 