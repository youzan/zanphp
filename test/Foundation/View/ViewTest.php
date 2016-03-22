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
    public $view = null;
    public $tplPath = '';
    public $tplData = [];

    public function setUp()
    {
        $this->tplPath = __DIR__ . '/Tpl/Content.html';
        $this->tplData = ['title' => '商品标题', 'content' => '商品详情'];
        $this->view = new View($this->tplPath, $this->tplData);
    }

    public function tearDown()
    {
        $this->view = null;
        $this->tplPath = '';
        $this->tplData = [];
    }

    public function testDisplay()
    {
        $html = View::display($this->tplPath, $this->tplData);
        $htmlExcepted = trim(file_get_contents(__DIR__ . '/Tpl/view_render_excepted.html'), " \r\n");

        $find = ["\r\n", "\n", "\r", " "];
        $replace = '';
        $html = str_replace($find, $replace, $html);
        $htmlExcepted = str_replace($find, $replace, $htmlExcepted);
        $this->assertEquals($htmlExcepted, $html, 'ViewTest::testDisplay fail');
    }

    public function testRender()
    {
        $html = $this->view->render($this->tplPath, $this->tplData);
        $htmlExcepted = trim(file_get_contents(__DIR__ . '/Tpl/view_render_excepted.html'), " \r\n");

        $find = ["\r\n", "\n", "\r", " "];
        $replace = '';
        $html = str_replace($find, $replace, $html);
        $htmlExcepted = str_replace($find, $replace, $htmlExcepted);
        $this->assertEquals($htmlExcepted, $html, 'ViewTest::testRender fail');
    }

    public function testGetViewVars()
    {
        $viewVars = $this->invoke($this->view, '_getViewVars');

        $this->assertArrayHasKey('js', $viewVars,'ViewTest::testGetViewVars fail');
        $this->assertArrayHasKey('tpl', $viewVars,'ViewTest::testGetViewVars fail');
        $this->assertArrayHasKey('css', $viewVars,'ViewTest::testGetViewVars fail');
        $this->assertArrayHasKey('layout', $viewVars,'ViewTest::testGetViewVars fail');
        $this->assertArrayHasKey('title', $viewVars,'ViewTest::testGetViewVars fail');
        $this->assertArrayHasKey('content', $viewVars,'ViewTest::testGetViewVars fail');
        $this->assertEquals('Zan\Framework\Foundation\View\Js', get_class($viewVars['js']), 'ViewTest::testGetViewVars:js fail');
        $this->assertEquals('Zan\Framework\Foundation\View\Tpl', get_class($viewVars['tpl']), 'ViewTest::testGetViewVars:tpl fail');
        $this->assertEquals('Zan\Framework\Foundation\View\Css', get_class($viewVars['css']), 'ViewTest::testGetViewVars:css fail');
        $this->assertEquals('Zan\Framework\Foundation\View\Layout', get_class($viewVars['layout']), 'ViewTest::testGetViewVars:layout fail');
        $this->assertEquals('商品标题', $viewVars['title'], 'ViewTest::testGetViewVars fail');
        $this->assertEquals('商品详情', $viewVars['content'], 'ViewTest::testGetViewVars fail');
    }
}