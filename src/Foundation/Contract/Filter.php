<?php

namespace Zan\Framework\Foundation\Contract;


interface Filter {

    public function doFilter($request, $response);
}