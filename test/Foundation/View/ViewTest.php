<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/3/10
 * Time: 下午10:23
 */

namespace Zan\Framework\Test\Foundation\View;

use Zan\Framework\Foundation\View\View;
use Zan\Framework\Foundation\View\Tpl;

class ViewTest extends \TestCase
{
    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public function testDisplay()
    {
        $html = View::display(__DIR__ . '/Tpl/Content.html', ['title' => '商品标题', 'content' => '商品详情']);
        $htmlExcepted = trim(file_get_contents(__DIR__ . '/Tpl/view_render_excepted.html'), " \r\n");

        $find = ["\r\n", "\n", "\r", " "];
        $replace = '';
        $html = str_replace($find, $replace, $html);
        $htmlExcepted = str_replace($find, $replace, $htmlExcepted);
        $this->assertEquals($htmlExcepted, $html, 'ViewTest::testDisplay fail');
    }
}