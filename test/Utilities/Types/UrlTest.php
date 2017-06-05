<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/9
 * Time: 下午1:50
 */

namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Utilities\Types\Time;
use Zan\Framework\Utilities\Types\URL;
use Zan\Framework\Sdk\Cdn\Qiniu;

class UrlTest extends \TestCase
{

    public function setUp()
    {
        $urlConfig = require __DIR__ . '/config/url.php';
        URL::setConfig($urlConfig);
    }

    public function tearDown()
    {
    }

    public function testSite()
    {
        $url = URL::site("/showcase/goods/allgoods?kdt_id=12312#abc", 'pay');
        $this->assertEquals('http://www.pay.com/showcase/goods/allgoods?kdt_id=12312#abc', $url, 'URL::site fail');

        $url = URL::site("http:/www.koudaitong.com/showcase");
        $this->assertEquals("http:/www.koudaitong.com/showcase", $url, 'URL::site fail');


        $_SERVER['HTTP_HOST'] = 'www.koudaitong.com';
        $url = URL::site("/showcase?kdt_id=123", true, URL::SCHEME_HTTPS);
        $this->assertEquals("https://www.koudaitong.com/showcase?kdt_id=123", $url, 'URL::site fail');

    }
}
