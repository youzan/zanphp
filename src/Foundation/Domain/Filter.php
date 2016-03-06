<?php

namespace Zan\Framework\Foundation\Domain;

use Zan\Framework\Network\Http\Request;
use Zan\Framework\Network\Http\Response;

abstract class Filter{

    public static function className()
    {
        return get_called_class();
    }

    abstract function init();

    abstract function doFilter(Request $request, Response $response);

    abstract function destruct();

}
