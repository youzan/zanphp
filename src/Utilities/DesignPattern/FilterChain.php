<?php

namespace Zan\Framework\Utilities\DesignPattern;


class FilterChain
{
    private $request = null;
    private $response = null;
    private $context = null;

    private $filters = [];

    public function __construct($request, $response, $context)
    {
        $this->request = $request;
        $this->response = $response;
        $this->context = $context;
        $this->filters = [];
    }

    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;
    }

    public function execute()
    {
        if(empty($this->filters)){
            return null;
        }

        foreach($this->filters as $filter){
            $filter->doFilter(
                $this->request,
                $this->response,
                $this->context
            );
        }
    }
}