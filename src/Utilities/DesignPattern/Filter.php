<?php

namespace Zan\Framework\Utilities\DesignPattern;


interface Filter
{
    public function doFilter($request, $response, $context);
}