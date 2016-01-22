<?php

namespace Zan\Framework\Foundation\Core;

class Filter implements \Zan\Framework\Foundation\Contract\Filter{

    protected $only;
    protected $except;

    public static function className()
    {
        return get_called_class();
    }

    public function doFilter($request, $response)
    {
        return true;
    }
}