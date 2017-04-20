<?php
use Zan\Framework\Testing\TaskTest;

/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/29
 * Time: 下午8:19
 */
class TcpClientTest extends TaskTest
{
    public function setUp()
    {

    }
    public function taskTcp()
    {
        $connection = (yield ConnectionManager::getInstance()->get("tcp.trace"));
        $tcpClient = new TcpClient($connection);

        yield $tcpClient->send($this->builder->getData());
    }
}