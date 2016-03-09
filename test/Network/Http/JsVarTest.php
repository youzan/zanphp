<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/3/9
 * Time: 上午10:57
 */

namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Http\JsVar;

class JsVarTest extends \TestCase
{
    private $_jsVar = null;

    public function setUp()
    {
        $this->_jsVar = new JsVar();
        $this->_jsVar->setSession('kdt_id', 1);
        $this->_jsVar->setConfig('run_mode', 'online');
        $this->_jsVar->setQuery('query_path', 'showcase/goods/index');
        $this->_jsVar->setEnv('platform', 'ios');
    }

    public function tearDown()
    {
        $this->_jsVar = null;
    }

    public function testGet(){
        $excepted = [
            'session' => ['kdt_id' => 1],
            'query' => ['query_path' => 'showcase/goods/index'],
            'config' => ['run_mode' => 'online'],
            'env' => ['platform' => 'ios'],
        ];
        $excepted = json_encode($excepted);
        $jsVarData = $this->_jsVar->get();
        $jsVarData = json_encode($jsVarData);
        $this->assertEquals($excepted, $jsVarData, 'JsVarTest::getData fail');
    }
} 