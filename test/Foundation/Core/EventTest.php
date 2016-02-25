<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/2
 * Time: 20:59
 */

namespace Zan\Framework\Test\Foundation\Core;

require __DIR__ . '/../../../' . 'src/Test.php';

use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Foundation\Core\EventChain;

class EventTest extends \UnitTest {
    private $dataMap = [];

    public function setUp() {
        parent::setUp();

        Event::clear();
        $this->dataMap = [];
    }

    public function tearDown() {
        parent::tearDown();

        Event::clear();
        $this->dataMap = [];
    }

    public function testOnceBindWorkFine() {
        $that = $this;
        Event::bind('test_once_bind_evt',function($args) use($that){
            $that->onceBindCb();
        });
        Event::fire('test_once_bind_evt');
        Event::fire('test_once_bind_evt');

        $this->assertEquals(2, count($this->dataMap), 'fire twice event work fail');
        $this->assertTrue(in_array('bind', $this->dataMap), 'fire twice event work fail');
    }

    public function testOnceWorkFine() {
        $that = $this;
        Event::once('test_once_evt',function($args) use($that){
            $that->onceTestCb();
        });
        Event::fire('test_once_evt');
        Event::fire('test_once_evt');

        $this->assertEquals(1, count($this->dataMap), 'fire twice event work fail');
        $this->assertTrue(in_array('once', $this->dataMap), 'fire twice event work fail');
    }

    public function testBindWorkFine() {
        $that = $this;
        Event::bind('test_bind_evt',function($args) use($that){
            $that->bindTestCb();
        });
        Event::fire('test_bind_evt');

        $this->assertArrayHasKey('test_bind',$this->dataMap,'event bind fail');
        $that->assertEquals('ok',$this->dataMap['test_bind'],'event bind fail');
    }

    public function testUnbindWorkFine() {
        $that = $this;
        $bindCb = function($args) use($that){
            $that->bindTestCb();
        };

        Event::bind('test_bind_evt', $bindCb);
        Event::unbind('test_bind_evt', $bindCb);
        Event::fire('test_bind_evt');

        $this->assertArrayNotHasKey('test_bind', $this->dataMap,'event unbind fail');
    }

    public function testBeforeWorkFine() {
        $that = $this;
        $bindCb = function() use($that){
            $that->bindTestCb();
        };
        $beforeCb = function() use($that){
            $that->beforeTestCb();
        };

        Event::bind('test_bind_evt', $bindCb);
        Event::bind('test_before_evt', $beforeCb);
        EventChain::before('test_bind_evt', 'test_before_evt');
        Event::fire('test_bind_evt');

        $this->assertArrayHasKey('test_before',$this->dataMap,'event before fail');
        $that->assertEquals('ok',$this->dataMap['test_before'],'event before fail');
    }

    public function testAfterWorkFine() {
        $that = $this;
        $bindCb = function($args) use($that){
            $that->bindTestCb();
        };
        $afterCb = function($args) use($that){
            $that->afterTestCb();
        };

        Event::bind('test_bind_evt', $bindCb);
        Event::bind('test_after_evt', $afterCb);
        EventChain::after('test_bind_evt', 'test_after_evt');
        Event::fire('test_bind_evt');

        $this->assertArrayHasKey('test_after',$this->dataMap,'event after fail');
        $that->assertEquals('ok',$this->dataMap['test_after'],'event after fail');
    }

    public function testBreakChainWorkFine() {
        $that = $this;
        $bindCb = function($args) use($that){
            $that->bindTestCb();
        };
        $afterCb = function($args) use($that){
            $that->afterTestCb();
        };

        Event::bind('test_bind_evt', $bindCb);
        Event::bind('test_after_evt', $afterCb);
        EventChain::after('test_bind_evt', 'test_after_evt');
        EventChain::breakChain('test_bind_evt', 'test_after_evt');
        Event::fire('test_bind_evt');

        $this->assertArrayNotHasKey('test_after', $this->dataMap,'event unafter fail');
    }

    public function testChainWorkFine() {
        $that = $this;
        $chain1 = function($args) use($that){
            $that->chain1Test();
        };
        $chain2 = function($args) use($that){
            $that->chain2Test();
        };
        $chain3 = function($args) use($that){
            $that->chain3Test();
        };
        $chain4 = function($args) use($that){
            $that->chain4Test();
        };

        Event::bind('chain1',$chain1);
        Event::bind('chain2',$chain2);
        Event::bind('chain3',$chain3);
        Event::bind('chain4',$chain4);

        EventChain::join('chain1', 'chain2', 'chain3', 'chain4');
        Event::fire('chain1');

        $this->assertArrayHasKey('chain1',$this->dataMap,'event chain1 fail');
        $that->assertEquals('ok',$this->dataMap['chain1'],'event chain1 fail');

        $this->assertArrayHasKey('chain2',$this->dataMap,'event chain2 fail');
        $that->assertEquals('ok',$this->dataMap['chain2'],'event chain2 fail');

        $this->assertArrayHasKey('chain3',$this->dataMap,'event chain3 fail');
        $that->assertEquals('ok',$this->dataMap['chain3'],'event chain3 fail');

        $this->assertArrayHasKey('chain4',$this->dataMap,'event chain4 fail');
        $that->assertEquals('ok',$this->dataMap['chain4'],'event chain4 fail');
    }

    private function onceTestCb() {
        $this->dataMap[] = 'once';
    }

    private function onceBindCb() {
        $this->dataMap[] = 'bind';
    }

    private function bindTestCb() {
        $this->dataMap['test_bind'] = 'ok';
    }

    private function beforeTestCb() {
        $this->dataMap['test_before'] = 'ok';
    }

    private function afterTestCb() {
        $this->dataMap['test_after'] = 'ok';
    }

    private function chain1Test() {
        $this->dataMap['chain1'] = 'ok';
    }

    private function chain2Test() {
        $this->dataMap['chain2'] = 'ok';
    }

    private function chain3Test() {
        $this->dataMap['chain3'] = 'ok';
    }

    private function chain4Test() {
        $this->dataMap['chain4'] = 'ok';
    }
}
