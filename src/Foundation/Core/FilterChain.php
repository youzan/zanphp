<?php

namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Utilities\Types\Dir;

class FilterChain {

    public static $preFilters  = [];
    public static $postFilters = [];

    public static function loadPreFilters($preFilterPath)
    {
        $preFilters = Dir::glob($preFilterPath, '*.php');
    }

    public static function loadPostFilters($preFilterPath)
    {
        $postFilters = Dir::glob($preFilterPath, '*.php');
    }

    public static function doFilter($request, $response)
    {

    }

}