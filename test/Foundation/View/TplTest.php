<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/3/11
 * Time: 下午2:28
 */

namespace Zan\Framework\Test\Foundation\View;

use Zan\Framework\Foundation\View\Tpl;

class TplTest extends \TestCase
{
    public $tpl = null;

    public function setUp()
    {
        $this->tpl = new Tpl();
    }

    public function tearDown()
    {
        $this->tpl = null;
    }

    public function testLoad()
    {
        ob_start();
        $this->tpl->load('testTpl', ['a' => 1, 'b' => 2], __DIR__ . '/Tpl');
        $content = ob_get_clean();

        $contentExcepted = 'content';
        $this->assertEquals($contentExcepted, $content, 'LayoutTest::curLevel fail');
    }
} 