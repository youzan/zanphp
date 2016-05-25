<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/14
 * Time: 下午4:26
 */
namespace Zan\Framework\Test\Network\Server;

use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Contract\Network\Request;

class RequestTest implements Request{

    private $route;

    public function __construct($route){
        $this->route = $route;
    }

    public function getRoute(){
        return $this->route;
    }
}

class MiddlewareTest extends \TestCase {

    private $path;

    public function setUp()
    {
        $this->path = __DIR__ . '/MiddlewareConfig';
    }

    public function tearDown()
    {
    }

    public function testManage(){
        MiddlewareManager::instance()->loadConfig($this->path);

        $request = new RequestTest('/trade/test');
        $group = MiddlewareManager::instance()->getGroupValue($request);

        $this->assertContains( 'Acl', $group, 'MiddlewareManager::getGroupValue fail');
        $this->assertNotContains( 'Trade', $group, 'MiddlewareManager::getGroupValue fail');


        $request = new RequestTest('/trade/order/test?asdb=sad');
        $group = MiddlewareManager::instance()->getGroupValue($request);

        $this->assertContains( 'Acl', $group, 'MiddlewareManager::getGroupValue fail');
        $this->assertContains( 'Trade', $group, 'MiddlewareManager::getGroupValue fail');

    }

}
