<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/5/7
 * Time: 00:23
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