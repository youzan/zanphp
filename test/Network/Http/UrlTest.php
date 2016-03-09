<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/9
 * Time: 下午1:50
 */

namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Http\Url;

class UrlTest extends \TestCase
{

    public function setUp()
    {
        $config = require __DIR__.'/config/url.php';
        Url::setConfig($config);
    }

    public function tearDown()
    {
    }

    public function testSite(){
        $url = Url::site("/showcase/goods/allgoods?kdt_id=12312",'pay',Url::SCHEME_HTTPS);
        var_dump($url);
    }

}