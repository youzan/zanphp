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

namespace Zan\Framework\Foundation\View;


class JsVar
{
    /**
     * 用户账户相关
     * @var array
     */
    private $_session = [];
    /**
     * 配置相关
     * @var array
     */
    private $_config = [];
    /**
     * 请求相关
     * @var array
     */
    private $_query = [];
    /**
     * 运行时环境相关
     * @var array
     */
    private $_env = [];
    /**
     * 业务自身相关
     * @var array
     */
    private $_business = [];
    /**
     * 微信分享相关
     * @var array
     */
    private $_share = [];
    /**
     * 原iron的global中的url清单
     * @var array
     */
    private $_domain = [];

    public function setSession($key, $value)
    {
        $this->_session[$key] = $value;
    }

    public function setConfig($key, $value)
    {
        $this->_config[$key] = $value;
    }

    public function setQuery($key, $value)
    {
        $this->_query[$key] = $value;
    }

    public function setEnv($key, $value)
    {
        $this->_env[$key] = $value;
    }

    public function setBusiness($key, $value)
    {
        $this->_business[$key] = $value;
    }

    public function setShare($key, $value)
    {
        $this->_share[$key] = $value;
    }

    public function setDomain(array $domainList)
    {
        $this->_domain = $domainList;
    }

    public function get()
    {
        return [
            'session' => $this->_session,
            'config' => $this->_config,
            'query' => $this->_query,
            'env' => $this->_env,
            'business' => $this->_business,
            'share' => $this->_share,
            'url' => $this->_domain,
        ];
    }
} 