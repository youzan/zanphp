<?php

namespace Zan\Framework\Network\Http\Filter;

use Zan\Framework\Network\Http\Request;
use Zan\Framework\Network\Http\Response;

class FilterChain {

    private $step;
    private $preFilterChain;
    private $postFilterChain;

    public function __construct()
    {
        $this->setStepToPre();
    }

    public function doFilter(Request $request, Response $response)
    {
        $filterChain = $this->step == 'pre' ? $this->preFilterChain : $this->postFilterChain;

        if (empty($filterChain)) return true;

        foreach ($filterChain as $filter) {
            $filter.doFilter($request, $response);
        }
        return true;
    }

    public function setStepToPre()
    {
        $this->step = 'pre';
    }

    public function setStepToPost()
    {
        $this->step = 'post';
    }

    public function addPreFilter($filter)
    {
        $this->preFilterChain[] = $filter;
    }

    public function addPostFilter($filter)
    {
        $this->postFilterChain[] = $filter;
    }

}