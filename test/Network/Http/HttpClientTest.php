<?php
namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;
use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Testing\TaskTest;

class HttpClientTest extends TaskTest
{
    public function taskHttpGet()
    {
        $params = [
            'txt' => 'aaa',
            'size' => 200,
            'margin' => 20,
            'level' => 0,
            'hint' => 2,
            'case' => 1,
            'ver' => 1,
            'fg_color' => '000000',
            'bg_color' => 'ffffff',
        ];
        $httpClient = new HttpClient('127.0.0.1', 12345);
        try {
            $response = (yield $httpClient->get('', $params));
        } catch (\Exception $e) {
            $this->assertInstanceOf(HttpClientTimeoutException::class, $e, $e->getMessage());
            return;
        }

        $result = $response->getBody();

        $this->assertEquals($result, http_build_query($params), "Http request failed");
    }
}