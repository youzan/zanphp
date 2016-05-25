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
namespace Zan\Framework\Network\Http\Filter;

use Zan\Framework\Foundation\Domain\Filter;
use Zan\Framework\Foundation\Exception\System\FilterException;

class FilterLoader {

    private static $filterContainer;
    private static $preFilterKey  = 'pre';
    private static $postFilterKey = 'post';

    public static function loadFilter(array $filterConfig)
    {
        if (empty($filterConfig)) return;

        self::loadFilterClass(self::$preFilterKey, $filterConfig);
        self::loadFilterClass(self::$postFilterKey, $filterConfig);
    }

    private static function loadFilterClass($filterType, & $filterConfig)
    {
        if (empty(($filterList = $filterConfig[$filterType]))) return;

        foreach ($filterList as $filter) {

            if (isset(self::$filterContainer[$filterType][$filter])) {
                continue;
            }
            if (!$filter || !class_exists($filter)) {
                throw new FilterException('Not found filter:'.$filter);
            }
            $filterObj = new $filter();

            if (!($filterObj instanceof Filter)) {
                throw new FilterException('Is not an effective filter:'.$filter);
            }
            self::$filterContainer[$filterType] = $filterObj;

            self::addToFilterChain($filterType, $filterObj);
        }
    }

    private static function addToFilterChain($filterType, $filter)
    {
        if ($filterType == self::$preFilterKey) {
            FilterChain::instance()->addPreFilter($filter);
        }else {
            FilterChain::instance()->addPostFilter($filter);
        }
    }



}