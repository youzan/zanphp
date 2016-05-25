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

namespace Zan\Framework\Network\Server\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class MiddlewareConfig
{
    use Singleton;

    private $config = null;
    private $extendFilters = [];
    private $extendTerminators = [];

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setExtendFilters(array $extendFilters)
    {
        $this->extendFilters = $extendFilters;
    }

    public function setExtendTerminators(array $extendTerminators)
    {
        $this->extendTerminators = $extendTerminators;
    }

    public function getGroupValue(Request $request)
    {
        $route = $request->getRoute();
        $groupKey = null;

        for ($i = 0; ; $i++) {
            if (!isset($this->config['match'][$i])) {
                break;
            }
            $match = $this->config['match'][$i];
            $pattern = $match[0];
            if ($this->match($pattern, $route)) {
                $groupKey = $match[1];
                break;
            }
        }

        if (null === $groupKey) {
            return [];
        }
        if (!isset($this->config['group'][$groupKey])) {
            throw new InvalidArgumentException('Invalid Group name in MiddlewareManager');
        }

        return $this->config['group'][$groupKey];
    }

    public function match($pattern, $route)
    {
        if (preg_match($pattern, $route)) {
            return true;
        }
        return false;
    }


    public function addBaseFilters($filters)
    {
        $baseFilters = [

        ];
        return array_merge($baseFilters, $this->extendFilters, $filters);
    }

    public function addBaseTerminators($terminators)
    {
        $baseTerminators = [
        ];
        return array_merge($terminators, $this->extendTerminators, $baseTerminators);
    }
}