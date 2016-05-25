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

namespace Zan\Framework\Test\Foundation\Container;


use Zan\Framework\Foundation\Container\Container;
use Zan\Framework\Test\Foundation\Container\Stub\Demo;
use Zan\Framework\Test\Foundation\Container\Stub\Singleton;

class ContainerTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        parent::tearDown(); 
    }

    public function testMakeWork()
    {
        $container = new Container(); 
        
        $demoInstance = $container->make(Demo::class,[0,1]);
        $this->assertInstanceOf(Demo::class, $demoInstance, 'Container make instance failed');
        $this->assertEquals(0, $demoInstance->getArg0(), 'demoInstance made by container getArg0 failed');
    }
    
    public function testMakeSharedInstanceWork()
    {
        $container = new Container();
        
        $singleton = $container->make(Singleton::class,['zan'],true);
        $this->assertInstanceOf(Singleton::class,$singleton,'container make Singleton fail');
        $this->assertEquals('zan', $singleton->getUid(), 'singleton made by container getUid fail');

        $singleton = $container->make(Singleton::class,['zanxxxx'],true);
        $this->assertInstanceOf(Singleton::class,$singleton,'container share Singleton fail');
        $this->assertEquals('zan', $singleton->getUid(), 'container share singleton fail');
    }
    
    public function testSingletonWork()
    {
        
    }
}