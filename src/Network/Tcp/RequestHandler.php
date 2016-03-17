<?php

namespace Zan\Framework\Network\Tcp;

use \swoole_server as TcpServer;

class RequestHandler {

    public function __construct()
    {

    }

    public function handle(TcpServer $server, $fd, $from_id, $data)
    {
        $server->send($fd, $data);
    }


}