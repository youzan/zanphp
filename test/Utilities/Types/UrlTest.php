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

        $qiniuConfig = require __DIR__ . '/config/qiniu.php';
        Qiniu::setConfig($qiniuConfig);
    }

    public function tearDown()
    {
    }

    public function testSite()
    {
        $url = URL::site("/showcase/goods/allgoods?kdt_id=12312#abc", 'pay');
        $this->assertEquals('http://pay.koudaitong.com/showcase/goods/allgoods?kdt_id=12312#abc', $url, 'URL::site fail');

        $url = URL::site("http:/www.koudaitong.com/showcase");
        $this->assertEquals("http:/www.koudaitong.com/showcase", $url, 'URL::site fail');


        $_SERVER['HTTP_HOST'] = 'www.koudaitong.com';
        $url = URL::site("/showcase?kdt_id=123", true, URL::SCHEME_HTTPS);
        $this->assertEquals("https://www.koudaitong.com/showcase?kdt_id=123", $url, 'URL::site fail');

    }

    public function testCdnSite()
    {
        $url = URL::cdnSite('/upload_files/2016/01/06/Ftnfdi_-zrIVkmeRUCZoTT2Scagu.jpg', '!45x45.jpg');
        $this->assertEquals("https://dn-kdt-img.qbox.me/upload_files/2016/01/06/Ftnfdi_-zrIVkmeRUCZoTT2Scagu.jpg?imageView2/2/w/45/h/45/q/75/format/jpg", $url, 'URL::site fail');

        $url = URL::cdnSite('/upload_files/2016/01/06/Ftnfdi_-zrIVkmeRUCZoTT2Scagu.jpg!40X40.jpg', '!45x45.jpg', false, true);
        $this->assertEquals("https://dn-kdt-img.qbox.me/upload_files/2016/01/06/Ftnfdi_-zrIVkmeRUCZoTT2Scagu.jpg?imageView2/2/w/45/h/45/q/75/format/jpg", $url, 'URL::site fail');
    }

    public function testgetRequestUri(){
        $time = Time::current(true);
        var_dump($time);
    }
}
