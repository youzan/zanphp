<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
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
