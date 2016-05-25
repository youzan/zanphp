<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/3
 * Time: 上午10:53
 */
namespace Zan\Framework\Test\Foundation\Core;

use Zan\Framework\Foundation\Core\ConfigLoader;


class ConfigLoaderTest extends \TestCase {

    private $path;

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