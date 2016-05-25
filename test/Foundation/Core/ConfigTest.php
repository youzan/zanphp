<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:54
 */

namespace Zan\Framework\Test\Foundation\Core;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Foundation\Core\Path;


class ConfigTest extends \TestCase
{
    public function setUp()
    {
        $path = __DIR__ . '/config/';
        Path::setConfigPath($path);
    }

    public function tearDown()
    {
        Config::clear();
    }


    public function testGetConfigWork()
    {
        RunMode::set('online');
        Config::init();
        $data = Config::get('a.share');
        $this->assertEquals('share', $data, 'Config::get share get failed');
        $data = Config::get('a.config');
        $this->assertEquals('online', $data, 'Config::get share get failed');
        $data = Config::get('pf.b.test');
        $this->assertEquals('online', $data, 'Config::get share get failed');
        $data = Config::get('pf.b.db');
        $this->assertEquals('pf', $data, 'Config::get share get failed');
        Config::set('pf.b.new','new');
        $data = Config::get('pf.b.new');
        $this->assertEquals('new', $data, 'Config::set failed');
        Config::set('pf','delete');
        $data = Config::get('pf');
        $this->assertEquals('delete', $data, 'Config::set failed');
    }

}