<?php
/**
 * @author hupp
 * create date: 16/03/12
 */
namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Foundation\Coroutine\Context;

class ClientTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $path = __DIR__ . '/config/';

        Path::setConfigPath($path);
        RunMode::set('dev');

        Config::init();
        Config::get('http.client');
    }

    public function testClientCall()
    {
        $context = new Context();
        $job = new HttpClient($context);
        $coroutine = $job->call();

        $task = new Task($coroutine);
        $task->run();

        //var_dump($context->show());

    }

}