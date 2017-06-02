<?php
namespace Zan\Framework\Network\WebSocket;

use Zan\Framework\Contract\Network\Request as BaseRequest;
use Zan\Framework\Network\Http\Routing\Router;

class Request implements BaseRequest
{
    const WEBSOCKET_OPCODE_CONTINUATION_FRAME = 0x0;
    const WEBSOCKET_OPCODE_TEXT_FRAME = 0x1;
    const WEBSOCKET_OPCODE_BINARY_FRAME = 0x2;
    const WEBSOCKET_OPCODE_CONNECTION_CLOSE = 0x8;
    const WEBSOCKET_OPCODE_PING = 0x9;
    const WEBSOCKET_OPCODE_PONG = 0xa;

    private $data;
    private $route;
    private $path;
    private $fd;
    private $startTime;
    private $opcode;

    public function __construct($fd, $opcode, $path, $data)
    {
        $this->fd = $fd;
        $this->opcode = $opcode;
        $this->path = $path;
        $this->data = $data;
        $this->formatRoute();
    }

    private function formatRoute()
    {
        $router = Router::getInstance();
        $this->route = $router->handleUri($this->path);
    }

    public function getFd()
    {
        return $this->fd;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getOpcode()
    {
        return $this->opcode;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setStartTime()
    {
        $this->startTime = microtime(true);
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}