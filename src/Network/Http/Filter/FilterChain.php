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
namespace Zan\Framework\Network\Http\Filter;

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

    public function doFilter(Request $request, Response $response)
    {
        $filterChain = $this->step == 'pre' ? $this->preFilterChain : $this->postFilterChain;

        if (empty($filterChain)) return true;

        /** @var  $filter Filter*/
        foreach ($filterChain as $filter) {
            $filter->init();
            $filter->doFilter($request, $response);
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