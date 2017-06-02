<?php
namespace Zan\Framework\Network\WebSocket;

use Zan\Framework\Contract\Network\Response as BaseResponse;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\Types\Json;
use \swoole_websocket_server as SwooleWebSocketServer;

class Response implements BaseResponse
{

    const ERR_CODE_CONTINUE_UNSUPPORTED = 1;
    const ERR_CODE_ARGS_INVALID = 2;
    const ERR_CODE_INTERNAL_ERROR = 3;
    const ERR_CODE_REQUEST_TIMEOUT = 4;

    private static $errMsg = [
        self::ERR_CODE_CONTINUE_UNSUPPORTED => "WebSocket continue frame is unsupported",
        self::ERR_CODE_ARGS_INVALID     => "Request args is invalid",
        self::ERR_CODE_INTERNAL_ERROR   => "Internal error",
        self::ERR_CODE_REQUEST_TIMEOUT  => "Request timeout"
    ];

    /* @var $swooleWebSocketServer SwooleWebSocketServer */
    private $swooleWebSocketServer;

    private $fd;

    public function __construct(SwooleWebSocketServer $swooleWebSocketServer, $fd)
    {
        $this->swooleWebSocketServer = $swooleWebSocketServer;
        $this->fd = $fd;
    }

    public function fail($code, $data = null)
    {
        if (is_null($data)) {
            if (!isset(self::$errMsg[$code]))
                throw new InvalidArgumentException("code: $code is invalid");
            $data = self::$errMsg[$code];
        }
        $this->send($this->fd, $code, $data);
    }

    public function success($data)
    {
        $this->send($this->fd, 0, $data);
    }

    public function send($fd, $code, $data)
    {
        $msg = Json::encode([
            "code" => $code,
            "data" => $data
        ]);
        $this->swooleWebSocketServer->push($fd, $msg);
    }
}
