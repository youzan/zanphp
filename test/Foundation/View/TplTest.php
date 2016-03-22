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