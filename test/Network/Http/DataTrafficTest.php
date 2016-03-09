<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/3/9
 * Time: 上午10:52
 */

namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Http\DataTraffic;

class DataTrafficTest extends \TestCase
{
    private $dataTraffic = null;

    public function setUp()
    {
        $this->path = __DIR__ . '/config/online';
    }

    public function tearDown()
    {
    }

    public function test(){
        $config = ConfigLoader::getInstance();
        $result = $config->load($this->path);
        $this->assertEquals('online', $result['a']['config'], 'ConfigLoader::load fail');
        $this->assertEquals('pf', $result['pf']['b']['db'], 'ConfigLoader::load fail');
        $this->assertEquals('pf', $result['pf']['a']['a'], 'ConfigLoader::load fail');
        $this->assertEquals('online', $result['pf']['b']['test'], 'ConfigLoader::load fail');
    }
} 