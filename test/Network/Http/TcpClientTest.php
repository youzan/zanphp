<?php
use Zan\Framework\Network\Common\TcpClient;
use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Testing\TaskTest;

/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/29
 * Time: 下午8:19
 */
class TcpClientTest extends TaskTest
{
    public function taskTcp()
    {
        $connection = (yield ConnectionManager::getInstance()->get("tcp.echo"));
        $tcpClient = new TcpClient($connection);

        $request = "Hello TcpClientTest";
        $response = (yield $tcpClient->send($request));
        $this->assertEquals($response, $request, "tcp echo test failed");
    }
}