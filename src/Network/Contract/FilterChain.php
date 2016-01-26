<?php

namespace Zan\Framework\Network\Contract;

abstract class FilterChain {

    abstract function doFilter(Request $request, Response $response);
}