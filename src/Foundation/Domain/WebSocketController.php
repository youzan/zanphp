<?php
namespace Zan\Framework\Foundation\Domain;

use Zan\Framework\Network\WebSocket\Request;
use Zan\Framework\Network\WebSocket\Response;
use Zan\Framework\Utilities\DesignPattern\Context;

class WebSocketController extends Controller {
    public function __construct(Request $request, Context $context)
    {
        parent::__construct($request, $context);
    }

    public function sendRaw($fd, $code, $msg)
    {
        /** @var Response $response */
        $response = $this->context->get("swoole_response");
        $response->send($fd, $code, $msg);
    }

    public function send($code, $msg)
    {
        $this->sendRaw($this->request->getFd(), $code, $msg);
    }

    public function output($msg)
    {
        $this->send(0, $msg);
    }
}