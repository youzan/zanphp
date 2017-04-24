<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/4/10
 * Time: 下午4:21
 */
namespace Zan\Framework\Testing\Sdk\ShortURL;

use Zan\Framework\Testing\TaskTest;
use Zan\Framework\Utilities\Types\ShortUrl;

class ShortURLTest extends TaskTest {
    public function taskGetShortURL()
    {
        $shortUrl = (yield ShortUrl::get("http://koudaitong.com"));
        $this->assertStringStartsWith("http://kdt.im/", $shortUrl, "url: $shortUrl is invalid, int should start with http://kdt.im/");
    }
}