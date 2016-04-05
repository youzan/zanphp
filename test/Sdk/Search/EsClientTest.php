<?php

require __DIR__ . '/../../bootstrap.php';

//namespace Zan\Framework\Test\Sdk\Searcher;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Sdk\Search\EsClient;

class EsClientTest extends \TestCase
{
    public function testSearch()
    {
        $context = new Context();

        $coroutine = $this->search();

        $task = new Task($coroutine, $context, 19);
        $task->run();

//        $result = $context->show();
//
//        $this->assertArrayHasKey('parallel_value',$result, 'parallel job failed to set context');
//        $this->assertEquals(4, count($result['parallel_value']), 'parallel result number get wrong');
//        $this->assertEquals($context->get('first_coroutine'), $result['parallel_value'][0], 'parallel callback 1 get wrong context value');
//        $this->assertEquals($context->get('second_coroutine'), $result['parallel_value'][1], 'parallel callback 2 get wrong context value');
//        $this->assertEquals($context->get('third_coroutine'), $result['parallel_value'][2], 'parallel callback 3 get wrong context value');
//        $this->assertInternalType('int', $result['parallel_value'][3], 'parallel callback 4 get wrong context value');
//
//        $taskData = $task->getResult();
//        $this->assertEquals('SysCall.Parallel', $taskData, 'get Parallel task final output fail');
    }

    public function search()
    {
        $nodeInfo = [
            'hosts' => [
                '10.6.1.219:8082',
            ],
        ];

        $params = array (
            'index' => 'fenxiao_goods_online',
            'type' => 'goods',
            'body' =>
                array (
                    'filter' =>
                        array (
                            'bool' =>
                                array (
                                    'must' =>
                                        array (
                                            0 =>
                                                array (
                                                    'terms' =>
                                                        array (
                                                            'fx_auth' =>
                                                                array (
                                                                    0 => 1,
                                                                    1 => 3,
                                                                ),
                                                        ),
                                                ),
                                            1 =>
                                                array (
                                                    'terms' =>
                                                        array (
                                                            'recommend_level' =>
                                                                array (
                                                                    0 => 20,
                                                                    1 => 30,
                                                                ),
                                                        ),
                                                ),
                                            2 =>
                                                array (
                                                    'terms' =>
                                                        array (
                                                            'sold_status' =>
                                                                array (
                                                                    0 => 1,
                                                                    1 => 3,
                                                                ),
                                                        ),
                                                ),
                                            3 =>
                                                array (
                                                    'term' =>
                                                        array (
                                                            'is_display' => 1,
                                                        ),
                                                ),
                                            4 =>
                                                array (
                                                    'missing' =>
                                                        array (
                                                            'field' => 'kdt_id_fake:1',
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                    'size' => 2,
                    'sort' =>
                        array (
                            0 =>
                                array (
                                    'fx_display_order' =>
                                        array (
                                            'order' => 'desc',
                                        ),
                                ),
                            1 =>
                                array (
                                    'sold_num' =>
                                        array (
                                            'order' => 'desc',
                                        ),
                                ),
                            2 => '_score',
                        ),
                    'query' =>
                        array (
                            'boosting' =>
                                array (
                                    'positive' =>
                                        array (
                                            'bool' =>
                                                array (
                                                    'should' =>
                                                        array (
                                                            0 =>
                                                                array (
                                                                    'match' =>
                                                                        array (
                                                                            'title' =>
                                                                                array (
                                                                                    'query' => '分销',
                                                                                    'minimum_should_match' => '100%',
                                                                                    'boost' => 2,
                                                                                ),
                                                                        ),
                                                                ),
                                                            1 =>
                                                                array (
                                                                    'match' =>
                                                                        array (
                                                                            'title' =>
                                                                                array (
                                                                                    'query' => '分销',
                                                                                    'minimum_should_match' => '75%',
                                                                                ),
                                                                        ),
                                                                ),
                                                        ),
                                                ),
                                        ),
                                    'negative' =>
                                        array (
                                            'bool' =>
                                                array (
                                                    'should' =>
                                                        array (
                                                            0 =>
                                                                array (
                                                                    'term' =>
                                                                        array (
                                                                            'is_review_passed' => 0,
                                                                        ),
                                                                ),
                                                        ),
                                                ),
                                        ),
                                    'negative_boost' => 0.59999999999999998,
                                ),
                        ),
                ),
        );

        $client = EsClient::newInstance($nodeInfo)->setParams([]);
        $result = (yield $client->info());
        var_dump('aa');
    }

    public function tearDown()
    {
        swoole_event_exit();
    }
}

(new EsClientTest())->testSearch();