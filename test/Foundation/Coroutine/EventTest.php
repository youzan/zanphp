<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/2
 * Time: 20:59
 */

namespace Zan\Framework\Test\Foundation\Coroutine;

use Zan\Framework\Foundation\Coroutine\Event;

class EventTest extends \TestCase {
    private $dataMap = [];

    public function setUp() {
        parent::setUp();

        $this->dataMap = [];
    }

    public function tearDown() {
        parent::tearDown();

        $this->dataMap = [];
    }

    public function testBindWorkFine() {
        $that = $this;
        $event = new Event();
        $event->bind('test_bind_evt',function($args) use($that){
            $that->bindTestCb();
        });
        $event->fire('test_bind_evt');

        $this->assertArrayHasKey('test_bind',$this->dataMap,'event bind fail');
        $that->assertEquals('ok',$this->dataMap['test_bind'],'event bind fail');
    }

    public function testUnbindWorkFine() {
        $that = $this;
        $event = new Event();
        $bindCb = function($args) use($that){
            $that->bindTestCb();
        };

        $event->bind('test_bind_evt', $bindCb);
        $event->unbind('test_bind_evt', $bindCb);
        $event->fire('test_bind_evt');

        $this->assertArrayNotHasKey('test_bind', $this->dataMap,'event unbind fail');
    }

    public function testBeforeWorkFine() {
        $that = $this;
        $event = new Event();
        $eventChain = $event->getEventChain();
        $bindCb = function() use($that){
            $that->bindTestCb();
        };
        $beforeCb = function() use($that){
            $that->beforeTestCb();
        };

        $event->bind('test_bind_evt', $bindCb);
        $event->bind('test_before_evt', $beforeCb);
        $eventChain->before('test_bind_evt', 'test_before_evt');
        $event->fire('test_bind_evt');

        $this->assertArrayHasKey('test_before',$this->dataMap,'event before fail');
        $that->assertEquals('ok',$this->dataMap['test_before'],'event before fail');
    }

    public function testAfterWorkFine() {
        $that = $this;
        $event = new Event();
        $eventChain = $event->getEventChain();
        $bindCb = function($args) use($that){
            $that->bindTestCb();
        };
        $afterCb = function($args) use($that){
            $that->afterTestCb();
        };

        $event->bind('test_bind_evt', $bindCb);
        $event->bind('test_after_evt', $afterCb);
        $eventChain->after('test_bind_evt', 'test_after_evt');
        $event->fire('test_bind_evt');

        $this->assertArrayHasKey('test_after',$this->dataMap,'event after fail');
        $that->assertEquals('ok',$this->dataMap['test_after'],'event after fail');
    }

    public function testBreakChainWorkFine() {
        $that = $this;
        $event = new Event();
        $eventChain = $event->getEventChain();
        $bindCb = function($args) use($that){
            $that->bindTestCb();
        };
        $afterCb = function($args) use($that){
            $that->afterTestCb();
        };

        $event->bind('test_bind_evt', $bindCb);
        $event->bind('test_after_evt', $afterCb);
        $eventChain->after('test_bind_evt', 'test_after_evt');
        $eventChain->breakChain('test_bind_evt', 'test_after_evt');
        $event->fire('test_bind_evt');

        $this->assertArrayNotHasKey('test_after', $this->dataMap,'event unafter fail');
    }

    public function testChainWorkFine() {
        $that = $this;
        $event = new Event();
        $eventChain = $event->getEventChain();
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

        $event->bind('chain1',$chain1);
        $event->bind('chain2',$chain2);
        $event->bind('chain3',$chain3);
        $event->bind('chain4',$chain4);

        $eventChain->join('chain1', 'chain2', 'chain3', 'chain4');
        $event->fire('chain1');

        $this->assertArrayHasKey('chain1',$this->dataMap,'event chain1 fail');
        $that->assertEquals('ok',$this->dataMap['chain1'],'event chain1 fail');

        $this->assertArrayHasKey('chain2',$this->dataMap,'event chain2 fail');
        $that->assertEquals('ok',$this->dataMap['chain2'],'event chain2 fail');

        $this->assertArrayHasKey('chain3',$this->dataMap,'event chain3 fail');
        $that->assertEquals('ok',$this->dataMap['chain3'],'event chain3 fail');

        $this->assertArrayHasKey('chain4',$this->dataMap,'event chain4 fail');
        $that->assertEquals('ok',$this->dataMap['chain4'],'event chain4 fail');
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
