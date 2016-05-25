<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\ExceptionHandlerChain;
use Zan\Framework\Network\Http\Exception\Handler\BizErrorHandler;
use Zan\Framework\Network\Http\Exception\Handler\InternalErrorHandler;
use Zan\Framework\Network\Http\Exception\Handler\InvalidRouteHandler;
use Zan\Framework\Network\Http\Exception\Handler\PageNotFoundHandler;
use Zan\Framework\Network\Http\Exception\Handler\RedirectHandler;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class RequestExceptionHandlerChain extends ExceptionHandlerChain
{
    use Singleton;

    private $handles = [
        RedirectHandler::class,
        PageNotFoundHandler::class,
        InvalidRouteHandler::class,
        BizErrorHandler::class,
        InternalErrorHandler::class,
    ];

    public function init()
    {
        $this->addHandlersByName($this->handles);
    }
}
