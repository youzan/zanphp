<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:20
 */


namespace Zan\Framework\Test\Store\Database;
require __DIR__ . '/../../../' . 'src/Test.php';

use Zan\Framework\Foundation\Test\UnitTest;

use Zan\Framework\Test\Foundation\Coroutine\Context;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Store\Database\Sql\Task\InsertJob;
use Zan\Framework\Test\Store\Database\Sql\Task\SelectJob;
use Zan\Framework\Test\Store\Database\Sql\Task\UpdateJob;
use Zan\Framework\Store\Database\Mysql\QueryResult;
class MysqlTest extends UnitTest
{
/*
    public function testInsert()
    {
        $context = new Context();
        $job = new InsertJob($context);
        $sid = 'demo.insert';
        $data = [
            'insert' => [
                'name' => "''a'''''",
                'nick_name' => 'b',
                'id_number' => '330323198888888888',
                'gender' => 0
            ]
        ];
        $options = [];
        $job->setSid($sid)->setInsert($data)->setOptions($options);
        $coroutine = $job->run();

        $task = new Task($coroutine);
        $task->run();
        $result = $context->show();
        $this->assertGreaterThan(0, $result['response']);
    }

    public function testUpdate()
    {
        $context = new Context();
        $job = new UpdateJob($context);
        $sid = 'demo.demo_sql_update1';
        $data = [
            'data' => [
                'name' => "abcd",
                'nick_name' => 'b',
                'id_number' => '330323198888888888',
                'gender' => 1
            ],
            'var' => ['name' => 'allen'],
            'and' => [
                ['gender', '=', 0],
            ],
            'and1' => [
                ['id_number', '=', 0],
            ],

        ];
        $options = [];
        $job->setSid($sid)->setData($data)->setOptions($options);
        $coroutine = $job->run();

        $task = new Task($coroutine);
        $task->run();
        $result = $context->show();
        $this->assertTrue($result['response']);
    }
*/
    public function testSelectOne()
    {
        $context = new Context();
        $job = new SelectJob($context);
        $sid = 'demo.demo_sql_id1_1';
        $data = [
            'var' => ['name' => 'a', 'nick_name' => 'b'],
            'and' => [
                ['gender', '=', 1],
            ],
            'and1' => [
                ['id_number', '=', 2147483647],
            ],

        ];
        $options = [];
        $job->setSid($sid)->setData($data)->setOptions($options);
        $coroutine = $job->run();

        $task = new Task($coroutine);
        $task->run();
        //$result = $context->show();
        //var_dump($result);
        //exit;
        //$this->assertTrue($result['response'] instanceof QueryResult);
        //$this->assertArrayHasKey('name', $result['response']->one());
    }

/*
    public function testSelectByWhere()
    {
        $context = new Context();
        $job = new SelectJob($context);
        $sid = 'demo.demo_sql_id2';
        $data = [
            'where' => [
                ['name', '=', 'a'], ['nick_name', '=', 'b']
            ],
            'order' => 'id desc',
            'group' => 'name',
            'limit' => 1,

        ];
        $options = [];
        $job->setSid($sid)->setData($data)->setOptions($options);
        $coroutine = $job->run();

        $task = new Task($coroutine);
        $task->run();
        $result = $context->show();
        $this->assertTrue($result['response'] instanceof QueryResult);
        $this->assertArrayHasKey('name', $result['response']->one());
    }
    public function testSelectRequireLimit()
    {

    }


*/

}