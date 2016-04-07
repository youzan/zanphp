<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http\Routing;

use Zan\Framework\Network\Http\Routing\UrlRule;

class TaskTest extends \PHPUnit_Framework_TestCase {

    public function testUrlRuleLoad()
    {
        $rulePath = __DIR__ . '/routing_new/';
        UrlRule::loadRules($rulePath);
        $ruleMap = UrlRule::getRules();
        $this->assertEquals(3, count($ruleMap), 'UrlRule::loadRules fail');
    }
}

