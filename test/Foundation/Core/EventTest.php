<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/2
 * Time: 20:59
 */

namespace Zan\Framework\Test\Foundation\Core;

require __DIR__ . '/../../../' . 'src/Zan.php';

use Zan\Framework\Foundation\Core\Event;

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

    public function testBindWorkFine() {
        $that = $this;
        Event::bind('test_bind_evt',function() use($that){
            $that->bindTestCb();
        });
        Event::fire('test_bind_evt');

        $this->assertArrayHasKey('test_bind',$this->dataMap,'event bind fail');
        $that->assertEquals('ok',$this->dataMap['test_bind'],'event bind fail');
    }

    public function testUnbindWorkFine() {
        $that = $this;
        $bindCb = function() use($that){
            $that->bindTestCb();
        };

        Event::bind('test_bind_evt', $bindCb);
        Event::unbind('test_bind_evt', $bindCb);
        Event::fire('test_bind_evt');

        $this->assertArrayNotHasKey('test_bind', $this->dataMap,'event unbind fail');
    }

    public function testAfterWorkFine() {
        $that = $this;
        $bindCb = function() use($that){
            $that->bindTestCb();
        };
        $afterCb = function() use($that){
            $that->afterTestCb();
        };

        Event::bind('test_bind_evt', $bindCb);
        Event::bind('test_after_evt', $afterCb);
        Event::after('test_bind_evt', 'test_after_evt');
        Event::fire('test_bind_evt');

        $this->assertArrayHasKey('test_after',$this->dataMap,'event after fail');
        $that->assertEquals('ok',$this->dataMap['test_after'],'event after fail');
    }

    public function testUnafterWorkFine() {
        $that = $this;
        $bindCb = function() use($that){
            $that->bindTestCb();
        };
        $afterCb = function() use($that){
            $that->afterTestCb();
        };

        Event::bind('test_bind_evt', $bindCb);
        Event::bind('test_after_evt', $afterCb);
        Event::after('test_bind_evt', 'test_after_evt');
        Event::unafter('test_bind_evt', 'test_after_evt');
        Event::fire('test_bind_evt');

        $this->assertArrayNotHasKey('test_after', $this->dataMap,'event unafter fail');
    }

    private function bindTestCb() {
        $this->dataMap['test_bind'] = 'ok';
    }

    private function afterTestCb() {
        $this->dataMap['test_after'] = 'ok';
    }
}