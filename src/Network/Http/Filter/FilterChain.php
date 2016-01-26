<?php

namespace Zan\Framework\Network\Http\Filter;

use Zan\Framework\Foundation\Coroutine\Context;
use Zan\Framework\Foundation\Domain\Filter;
use Zan\Framework\Network\Http\Request;
use Zan\Framework\Network\Http\Response;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class FilterChain {

    use Singleton;

    private $step;
    private $preFilterChain;
    private $postFilterChain;

    public function __construct()
    {
        $this->setStepToPre();
    }

    public function doFilter(Request $request, Response $response, Context $context)
    {
        $filterChain = $this->step == 'pre' ? $this->preFilterChain : $this->postFilterChain;

        if (empty($filterChain)) return true;

        /** @var  $filter Filter*/
        foreach ($filterChain as $filter) {
            $filter->init();
            $filter->doFilter($request, $response, $context);
            $filter->destruct();
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