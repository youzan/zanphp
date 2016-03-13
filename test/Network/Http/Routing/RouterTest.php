<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http\Routing;

use Zan\Framework\Network\Http\Routing\Router;
use Zan\Framework\Network\Http\Routing\UrlRule;
use Zan\Framework\Test\Network\Http\Request;

class RouterTest extends \PHPUnit_Framework_TestCase {

    public function testRouter()
    {
        $defaultRouteConf = include 'route.php';
        UrlRule::loadRules(__DIR__ . '/routing/');

        $router  = new Router($defaultRouteConf);
        $request = new Request();
        $request->setUrl('http://127.0.0.1:5601');
        list($route, $params) = $router->parse($request);

        $this->assertEquals('order/homePage/index', $this->formatRoute($route), 'Routing parse error!');
        $this->assertEquals([], $params, 'Error in routing parameter analysis!');

        $request->setUrl('http://127.0.0.1:5601/detail/E123/1');
        list($route, $params) = $router->parse($request);

        $this->assertEquals('order/book/detail', $this->formatRoute($route), 'Routing parse error!');
        $this->assertEquals(['order_no'=>'E123', 'kdt_id'=>1], $params, 'Error in routing parameter analysis!');

        $request->setUrl('http://127.0.0.1:5601/order/book');
        list($route, $params) = $router->parse($request);

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
}

