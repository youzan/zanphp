<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\ExceptionHandlerChain;
use Zan\Framework\Network\Http\Exception\Handler\BizErrorHandler;
use Zan\Framework\Network\Http\Exception\Handler\ForbiddenHandler;
use Zan\Framework\Network\Http\Exception\Handler\InternalErrorHandler;
use Zan\Framework\Network\Http\Exception\Handler\InvalidRouteHandler;
use Zan\Framework\Network\Http\Exception\Handler\PageNotFoundHandler;
use Zan\Framework\Network\Http\Exception\Handler\RedirectHandler;
use Zan\Framework\Network\Http\Exception\Handler\ServerUnavailableHandler;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class RequestExceptionHandlerChain extends ExceptionHandlerChain
{
    use Singleton;

    private $handles = [
        RedirectHandler::class,
        PageNotFoundHandler::class,
        ForbiddenHandler::class,
        InvalidRouteHandler::class,
        BizErrorHandler::class,
        ServerUnavailableHandler::class,
        InternalErrorHandler::class,
    ];

    public function init()
    {
        $this->addHandlersByName($this->handles);
    }
}
