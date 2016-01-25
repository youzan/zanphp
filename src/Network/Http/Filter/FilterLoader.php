<?php

namespace Zan\Framework\Network\Http\Filter;

use Zan\Framework\Foundation\Domain\Filter;
use Zan\Framework\Foundation\Exception\System\FilterException;
use Zan\Framework\Utilities\Types\Dir;

class FilterLoader {

    private static $filterList;
    private static $filterKey = 'filter';

    private static $preFilterKey = 'pre';
    private static $postFilterKey = 'post';

    public static function loadFilter($filerPath='')
    {
        if (empty($filerPath)) return;

        Dir::

        self::loadFilterClass(self::$preFilterKey, $filterConfig);
        self::loadFilterClass(self::$postFilterKey, $filterConfig);
    }

    private static function loadFilterClass($type, & $filterConfig)
    {
        if (empty(($filterList = $filterConfig[$type]))) return;

        foreach ($filterList as $filter) {
            $filterName = $filter['filer_name'];
            if (isset(self::$filterList[$type][$filterName])) {
                continue;
            }
            if (!$filterName || !class_exists($filterName)) {
                throw new FilterException('Not found filter:'.$filterName);
            }
            $filterObj = new $filter['filter_name']();
            if (!($filterObj instanceof Filter)) {
                throw new FilterException('Is not an effective filter:'.$filterName);
            }
            self::$filterList[$type] = $filterObj;
        }
    }

    public static function getPreFilters()
    {
        return self::$filterList[self::$preFilterKey];
    }

    public static function getPostFilters()
    {
        return self::$filterList[self::$postFilterKey];
    }

}