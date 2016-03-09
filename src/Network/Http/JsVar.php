<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/3/8
 * Time: 下午9:47
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Network\Http\DataTraffic;
use Zan\Framework\Foundation\Core\Config;

class JsVar
{
    private $_session = [];
    private $_query   = [];
    private $_config  = [];
    private $_env     = [];

    public function setSession($key, $value)
    {
        $this->_session[$key] = $value;
    }

    public function setQuery($key, $value)
    {
        $this->_query[$key] = $value;
    }

    public function setConfig($key, $value)
    {
        $this->_config[$key] = $value;
    }

    public function setEnv($key, $value)
    {
        $this->_env[$key] = $value;
    }

    public function get()
    {
        return [
            'session' => $this->_session,
            'query' => $this->_query,
            'config' => $this->_config,
            'env' => $this->_env
        ];
    }
}