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

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Network\Server\Middleware\MiddlewareInitiator;
use Zan\Framework\Foundation\Core\Config;

class InitializeMiddleware
{
    private $extendFilters = [
        //'filter1', 'filter2'
    ];

    private $extendTerminators = [
         //'terminator1', 'terminator2'
    ];

    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        $middlewareInitiator = MiddlewareInitiator::getInstance();
        $middlewareConfig = Config::get('middleware');
        $middlewareConfig = !is_array($middlewareConfig) || [] == $middlewareConfig ? [] : $middlewareConfig;
        $middlewareInitiator->initConfig($middlewareConfig);
        $middlewareInitiator->initExtendFilters($this->extendFilters);
        $middlewareInitiator->initExtendTerminators($this->extendTerminators);
    }
}