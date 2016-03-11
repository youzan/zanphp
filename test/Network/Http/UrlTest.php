<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/9
 * Time: 下午1:50
 */

namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Http\Url;
use Zan\Framework\Sdk\Cdn\Qiniu;

require __DIR__ . '/../../../' . 'src/Test.php';

class UrlTest extends \UnitTest
{

    public function setUp()
    {
        $urlConfig = require __DIR__.'/config/url.php';
        Url::setConfig($urlConfig);

        $qiniuConfig = require __DIR__.'/config/qiniu.php';
        Qiniu::setConfig($qiniuConfig);
    }

    public function tearDown()
    {
    }

    public function testSite(){
        $url = Url::site("/showcase/goods/allgoods?kdt_id=12312#abc",'pay');
        $this->assertEquals('http://pay.koudaitong.com/showcase/goods/allgoods?kdt_id=12312#abc',$url, 'Url::site fail');

        $url = Url::site("http:/www.koudaitong.com/showcase");
        $this->assertEquals("http:/www.koudaitong.com/showcase",$url, 'Url::site fail');


        $_SERVER['HTTP_HOST'] = 'www.koudaitong.com';
        $url = Url::site("/showcase?kdt_id=123",true,Url::SCHEME_HTTPS);
        $this->assertEquals("https://www.koudaitong.com/showcase?kdt_id=123",$url, 'Url::site fail');

    }

    public function testCdnSite(){
        $url = Url::cdnSite('/upload_files/2016/01/06/Ftnfdi_-zrIVkmeRUCZoTT2Scagu.jpg','!45x45.jpg');
        $this->assertEquals("https://dn-kdt-img.qbox.me/upload_files/2016/01/06/Ftnfdi_-zrIVkmeRUCZoTT2Scagu.jpg?imageView2/2/w/45/h/45/q/75/format/jpg",$url, 'Url::site fail');

        $url = Url::cdnSite('/upload_files/2016/01/06/Ftnfdi_-zrIVkmeRUCZoTT2Scagu.jpg!40X40.jpg','!45x45.jpg',false,true);
        $this->assertEquals("https://dn-kdt-img.qbox.me/upload_files/2016/01/06/Ftnfdi_-zrIVkmeRUCZoTT2Scagu.jpg?imageView2/2/w/45/h/45/q/75/format/jpg",$url, 'Url::site fail');
    }

}