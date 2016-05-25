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

use Zan\Framework\Foundation\View\Layout;
use Zan\Framework\Foundation\View\Tpl;
use Zan\Framework\Foundation\Coroutine\Event;

class LayoutTest extends \TestCase
{
    public $layout = null;
    public $tpl = null;
    public $event = null;
    public $tplPath = '';

    public function setUp()
    {
        $this->event = new Event();
        $this->tpl = new Tpl($this->event);
        $this->tplPath = __DIR__ . '/Tpl/Content.html';
        $this->layout = new Layout($this->tpl, $this->event, $this->tplPath);
    }

    public function tearDown()
    {
        $this->layout = null;
        $this->event = null;
        $this->tpl = null;
        $this->tplPath = '';
    }

    public function testBlockAndEndBlock()
    {
        $this->layout->block('title');
        echo 'content';
        $this->layout->endBlock('title');

        $curLevel = $this->getProperty($this->layout, 'curLevel');
        $blockQueue = $this->getProperty($this->layout, 'blockQueue');
        $blocks = $this->getProperty($this->layout, 'blocks');
        $blockLevelMap = $this->getProperty($this->layout, 'blockLevelMap');

        $curLevelExcepted = 0;
        $blockQueueExcepted = [];
        $blocksExcepted = ['TITLE' => 'content'];
        $blockLevelMapExcepted = [0 => ['TITLE']];

        $this->assertEquals($curLevelExcepted, $curLevel, 'LayoutTest::testBlockAndEndBlock::curLevel fail');
        $this->assertEquals($blockQueueExcepted, $blockQueue, 'LayoutTest::testBlockAndEndBlock::blockQueue fail');
        $this->assertEquals($blocksExcepted, $blocks, 'LayoutTest::testBlockAndEndBlock::blocks fail');
        $this->assertEquals($blockLevelMapExcepted, $blockLevelMap, 'LayoutTest::testBlockAndEndBlock::blockLevelMap fail');
    }

    public function testPlace()
    {
        $this->layout->place('title', 'content');
        $blocks = $this->getProperty($this->layout, 'blocks');
        $blockLevelMap = $this->getProperty($this->layout, 'blockLevelMap');

        $blocksExcepted = ['TITLE' => 'content'];
        $blockLevelMapExcepted = [0 => ['TITLE']];

        $this->assertEquals($blocksExcepted, $blocks, 'LayoutTest::testPlace::blocks fail');
        $this->assertEquals($blockLevelMapExcepted, $blockLevelMap, 'LayoutTest::testPlace::blockLevelMap fail');
    }

    public function testSuper()
    {
        $this->layout->block('title');
        echo 'content';
        $this->layout->endBlock('title');

        ob_start();
        $this->layout->super('title');
        $content = ob_get_clean();

        $contentExcepted = 'content';
        $this->assertEquals($contentExcepted, $content, 'LayoutTest::testSuper fail');
    }

    public function testGetCurrentBlock()
    {
        $this->setPropertyValue($this->layout, 'blockQueue', ['title', 'content']);
        $currentBlock = $this->invoke($this->layout, 'getCurrentBlock');

        $currentBlockExcepted = 'content';
        $this->assertEquals($currentBlockExcepted, $currentBlock, 'LayoutTest::testGetCurrentBlock fail');
    }

    public function testAddBlockToLevelMap()
    {
        $this->invoke($this->layout, 'addBlockToLevelMap', ['blockname' => 'title', 'level' => 1]);
        $blockLevelMap = $this->getProperty($this->layout, 'blockLevelMap');

        $blockLevelMapExcepted = [
            1 => [
                'title'
            ]
        ];
        $this->assertEquals($blockLevelMapExcepted, $blockLevelMap, 'LayoutTest::testAddBlockToLevelMap fail');
    }

    public function testReplaceBlocksOfOneLevel()
    {
        $tpl = '%%BLOCK__TITLE__BLOCK%%';
        $blocks = [
            'TITLE' => '这个是测试标题'
        ];
        $this->setPropertyValue($this->layout, 'blocks', $blocks);
        $content = $this->invoke($this->layout, 'replaceBlocksOfOneLevel', ['tpl' => $tpl, 'level' => ['TITLE']]);

        $contentExcepted = '这个是测试标题';
        $this->assertEquals($contentExcepted, $content, 'LayoutTest::testReplaceBlocksOfOneLevel fail');
    }

    public function testReplaceBlocksLevelByLevel()
    {
        $tpl = '%%BLOCK__TITLE__BLOCK%%，%%BLOCK__CONTENT__BLOCK%%';
        $blocks = [
            'TITLE' => '这个是测试标题',
            'CONTENT' => '这个是正文内容'
        ];
        $blockLevelMap = [
            0 => [
                'TITLE'
            ],
            1 => [
                'CONTENT'
            ]
        ];
        $this->setPropertyValue($this->layout, 'blocks', $blocks);
        $this->setPropertyValue($this->layout, 'blockLevelMap', $blockLevelMap);
        $content = $this->invoke($this->layout, 'replaceBlocksLevelByLevel', ['tpl' => $tpl, 'level' => ['TITLE', 'CONTENT']]);

        $contentExcepted = '这个是测试标题，这个是正文内容';
        $this->assertEquals($contentExcepted, $content, 'LayoutTest::testReplaceBlocksLevelByLevel fail');
    }
}