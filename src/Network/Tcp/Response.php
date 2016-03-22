<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 23:55
 */

namespace Zan\Framework\Network\Tcp;

use swoole_server as SwooleServer;
use Zan\Framework\Contract\Network\Response as BaseResponse;

class Response implements BaseResponse {
    /**
     * @var SwooleServer
     */
    private $swooleServer;
    private $fd;

    public function __construct(SwooleServer $swooleServer, $fd)
    {
        $this->swooleServer = $swooleServer;
        $this->fd = $fd;
    }

    public function end($content='')
    {
        return 'ok';
    }

    public function send($content)
    {

    }

}