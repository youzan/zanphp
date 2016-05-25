<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
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