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

