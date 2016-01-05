<?php

namespace Zan\Framework\Foundation\Core;

class Filter implements \Zan\Framework\Foundation\Contract\Filter{

    protected $only;
    protected $except;

    protected function doFilter()
    {
        return true;
    }

    public static function className()
    {
        return get_called_class();
    }

}