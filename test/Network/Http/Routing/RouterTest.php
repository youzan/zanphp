<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http\Routing;

use Zan\Framework\Network\Http\Routing\Router;
use Zan\Framework\Network\Http\Routing\UrlRule;
use swoole_http_request as SwooleHttpRequest;
use Zan\Framework\Network\Http\Request\Request;

class RouterTest extends \TestCase
{
    public $urlRulesFilePath = '';
    public $defaultRouteConfPath = '';

    public function setUp()
    {
        $this->urlRulesFilePath = __DIR__ . '/routing_new/';
        $this->defaultRouteConfPath = 'route.php';
    }

    public function testRouter()
    {
        $defaultRouteConf = include $this->defaultRouteConfPath;
        $router = new Router($defaultRouteConf);

        UrlRule::loadRules($this->urlRulesFilePath);

        $swooleHttpRequest = $this->mockSwooleHttpRequest();
        $request = Request::createFromSwooleHttpRequest($swooleHttpRequest);

        $router->route($request);

        var_dump($request->getRequestFormat());exit;




        $this->assertEquals('order/homePage/index', $this->formatRoute($route), 'Routing parse error!');
        $this->assertEquals([], $params, 'Error in routing parameter analysis!');

        $request->setUrl('http://127.0.0.1:5601/detail/E123/1');
        list($route, $params) = $router->route($request);

        $this->assertEquals('order/book/detail', $this->formatRoute($route), 'Routing parse error!');
        $this->assertEquals(['order_no'=>'E123', 'kdt_id'=>1], $params, 'Error in routing parameter analysis!');

        $request->setUrl('http://127.0.0.1:5601/order/book');
        list($route, $params) = $router->route($request);

        $this->assertEquals('order/book/index', $this->formatRoute($route), 'Routing parse error!');
        $this->assertEquals([], $params, 'Error in routing parameter analysis!');
    }

    private function formatRoute($route)
    {
        return $this->getModule($route['module']).'/'.
               $route['controller']. '/' .
               $route['action'];
    }

    private function getModule($module = [])
    {
        foreach ($module as $index => $name) {
            $module[$index] = $name;
        }
        return join('\\', $module);
    }

    private function mockSwooleHttpRequest()
    {
        $swooleHttpRequest = new SwooleHttpRequest();
        $swooleHttpRequest->header = [
            'host' => '127.0.0.1:8000',
            'connection' => 'close',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'upgrade-insecure-requests' => 1,
            'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36',
            'accept-encoding' => 'gzip, deflate, sdch',
            'accept-language' => 'zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4',
        ];
        $swooleHttpRequest->server = [
            'request_method' => 'GET',
            'request_uri' => '/goods/wxpay/123/xxx.json',
            'path_info' => '/market/create/index',
            'request_time' => '1459911416',
            'server_port' => '8000',
            'remote_port' => '55105',
            'remote_addr' => '127.0.0.1',
            'server_protocol' => 'HTTP/1.0',
            'server_software' => 'swoole-http-server'
        ];
        $swooleHttpRequest->cookie = [
            'KDTSESSIONID' => '21v241199of5n49s7fm8th7bp0',
        ];
        return $swooleHttpRequest;
    }
}

