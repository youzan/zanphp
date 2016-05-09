<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/9
 * Time: 上午9:46
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Store\Facade\KV;
use Zan\Framework\Utilities\Encrpt\Uuid;


class Session
{
    const YZ_SESSION_KEY = '__yz_session_id';
    const CONFIG_KEY = 'session';

    private $request;
    private $cookie;
    private $session_id;
    private $config;
    private $kv;
    private $ttl;

    public function __construct(Request $request, $cookie)
    {
        $this->config = Config::get(self::CONFIG_KEY);
        if (!$this->config['run']) {
            return;
        }

        $this->request = $request;
        $this->cookie = $cookie;
        $this->kv = KV::getInstance($this->config['kv_name']);
        $this->ttl = $this->config['ttl'];

        $this->init();
    }

    private function init()
    {
        $session_id = $this->request->cookie(self::YZ_SESSION_KEY);
        if (isset($session_id) && !empty($session_id)) {
            return;
        }

        $this->session_id = Uuid::get();
        $this->cookie->set(self::YZ_SESSION_KEY, $this->session_id);
    }

    public function set($key, $value)
    {
        //yield $this->kv->set($key, $value, $this->ttl);
    }

    public function get()
    {

    }
}