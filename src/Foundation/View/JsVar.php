<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/3/22
 * Time: 下午2:57
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