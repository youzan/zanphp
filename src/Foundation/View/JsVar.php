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
    private $_session = [];
    private $_config = [];
    private $_query = [];
    private $_env = [];
    private $_business = [];
    private $_share = [];
    private $_url = [];

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

    public function get()
    {
        return [
            'session' => $this->_session,
            'config' => $this->_config,
            'query' => $this->_query,
            'env' => $this->_env,
            'business' => $this->_business,
            'share' => $this->_share
        ];
    }
} 