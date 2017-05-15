<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/14
 * Time: 下午4:26
 */
namespace Zan\Framework\Test\Network\Server;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\ConfigLoader;
use Zan\Framework\Network\Server\Middleware\MiddlewareConfig;
use Zan\Framework\Contract\Network\Request;

class RequestTest implements Request {

    private $route;

    public function __construct($route){
        $this->route = $route;
    }

    public function getRoute(){
        return $this->route;
    }
}

class MiddlewareTest extends \TestCase {

    public function testManage(){
        $middlewareConfig = ConfigLoader::getInstance()->load(Config::get('path.middleware'));
        $middlewareConfig = isset($middlewareConfig['middleware']) ? $middlewareConfig['middleware'] : [];
        MiddlewareConfig::getInstance()->setConfig($middlewareConfig);

        $request = new RequestTest('/trade/test');
        $group = MiddlewareConfig::getInstance()->getRequestFilters($request);

        $this->assertContains( 'Acl', $group, 'MiddlewareManager::getGroupValue fail');
        $this->assertNotContains( 'Trade', $group, 'MiddlewareManager::getGroupValue fail');


        $request = new RequestTest('/trade/order/test?asdb=sad');
        $group = MiddlewareConfig::getInstance()->getRequestFilters($request);

        $this->assertContains( 'Acl', $group, 'MiddlewareManager::getGroupValue fail');
        $this->assertContains( 'Trade', $group, 'MiddlewareManager::getGroupValue fail');

    }

}
