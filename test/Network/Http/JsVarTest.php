<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/3/9
 * Time: 上午10:57
 */

namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Http\JsVar;
use Zan\Framework\Network\Http\DataTraffic;

class JsVarTest extends \TestCase
{
    private $_dataTraffic = null;
    private $_jsVar = null;
    private $_jsVarMapping = null;

    public function setUp()
    {
        $this->_dataTraffic = new DataTraffic();
        $this->_dataTraffic->setKdtId(1);
        $this->_dataTraffic->setRunMode('online');
        $this->_dataTraffic->setPlatform('ios');
        $this->_dataTraffic->setQueryPath('showcase/goods/index');

        $this->_jsVarMapping = [
            '_session' => [
                'kdt_id' => 'kdtId',
            ],
            '_config' => [
                'run_mode' => 'runMode',
            ],
            '_env' => [
                'platform' => 'platform',
            ],
            '_query' => [
                'query_path' => 'queryPath',
            ],
        ];
        $this->_jsVar = new JsVar($this->_dataTraffic);
    }

    public function tearDown()
    {
        $this->_dataTraffic = null;
        $this->_jsVar = null;
        $this->_jsVarMapping = null;
    }

    public function testGetData(){
        $excepted = [
            '_session' => ['kdt_id' => 1],
            '_config' => ['run_mode' => 'online'],
            '_env' => ['platform' => 'ios'],
            '_query' => ['query_path' => 'showcase/goods/index'],
        ];
        $excepted = json_encode($excepted);
        $jsVarData = $this->_jsVar->getData($this->_jsVarMapping);
        $jsVarData = json_encode($jsVarData);
        $this->assertEquals($excepted, $jsVarData, 'JsVarTest::getData fail');
    }
} 