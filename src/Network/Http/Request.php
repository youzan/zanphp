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
namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;

class Request extends \Zan\Framework\Network\Contract\Request{

    private $request;
    private $queryParams;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getRequestUri()
    {
        $requestUri = $this->request->server['request_uri'];

        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
        }
        if (!$requestUri)
            throw new InvalidArgument('Unable to determine the request URI.');

        return $requestUri;
    }

    public function getQueryParams()
    {
        if ($this->queryParams === null) {
            return $this->request->get;
        }
        return array_merge($this->request->get, $this->queryParams);
    }

}