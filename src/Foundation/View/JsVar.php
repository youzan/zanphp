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
            '_session' => $this->_session,
            '_config' => $this->_config,
            '_query' => $this->_query,
            '_env' => $this->_env,
            '_business' => $this->_business,
            '_share' => $this->_share
        ];
    }
} 