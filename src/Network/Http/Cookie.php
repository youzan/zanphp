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

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Http\Request\Request;
use swoole_http_response as SwooleHttpResponse;

class Cookie
{
    private $configKey = 'cookie';
    private $config;
    private $request;
    private $response;

    public function __construct(Request $request, SwooleHttpResponse $swooleResponse)
    {
        $this->init($request, $swooleResponse);
    }

    private function init(Request $request, SwooleHttpResponse $swooleResponse)
    {
        $config = Config::get($this->configKey, null);
        if (!$config) {
            throw new InvalidArgumentException('cookie config is required');
        }
        $this->config = $config;
        $this->request = $request;
        $this->response = $swooleResponse;
    }

    public function get($key, $default = null)
    {
        $cookies = $this->request->cookies;
        if (!$key) {
            yield $default;
        }

        yield $cookies->get($key, $default);
    }

    public function set($key, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        if (!$key) {
            return false;
        }
        if (null === $expire) {
            $expire = isset($this->config['expire']) ? $this->config['expire'] : 0;
        }
        $expire = time() + (int)$expire;

        $path = (null !== $path) ? $path : $this->config['path'];
        $domain = (null !== $domain) ? $domain : $this->request->getHost();
        $secure = (null !== $secure) ? $secure : $this->config['secure'];
        $httpOnly = (null !== $httpOnly) ? $httpOnly : $this->config['httponly'];

        $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

}
