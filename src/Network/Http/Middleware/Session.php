<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/9
 * Time: 上午9:46
 */

namespace Zan\Framework\Network\Http\Middleware;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Store\Facade\KV;
use Zan\Framework\Utilities\Encrpt\Uuid;


class Session
{
    const YZ_SESSION_KEY = 'KDTSESSIONID';
    const CONFIG_KEY = 'server.session';
    const SESSION_PREFIX = 'YZ_SESSION:';

    private $request;
    private $cookie;
    private $session_id;
    private $session_map = array();
    private $config;
    private $ttl;
    private $isChanged = false;

    public function __construct(Request $request, $cookie)
    {
        $this->config = Config::get(self::CONFIG_KEY);

        $this->request = $request;
        $this->cookie = $cookie;
        $this->ttl = $this->config['ttl'];

    }

    public function init()
    {
        if (!$this->config['run']) {
            yield false;
            return;
        }

        $session_id = $this->request->cookie(self::YZ_SESSION_KEY);
        if (isset($session_id) && !empty($session_id)) {
            $this->session_id = $session_id;
        } else {
            $this->session_id = Uuid::get();
            $this->cookie->set(self::YZ_SESSION_KEY, $this->session_id);
            yield true;
            return;
        }

        $session = (yield KV::get($this->config['kv'], self::SESSION_PREFIX.$this->session_id));
        if ($session) {
            $this->session_map = unserialize($session);
        }
        yield true;
    }

    public function set($key, $value)
    {
        $this->session_map[$key] = $value;
        $this->isChanged = true;
        yield true;
    }

    public function get($key)
    {
        yield isset($this->session_map[$key]) ? $this->session_map[$key] : null;
    }

    public function delete($key)
    {
        unset($this->session_map[$key]);
        $this->isChanged = true;
        yield true;
    }

    public function destory()
    {
        $ret = (yield KV::remove($this->config['kv'], self::SESSION_PREFIX . $this->session_id));
        if (!$ret) {
            yield false;
            return;
        }
        $this->cookie->set($this->session_id, null, time() - 3600);
        $this->isChanged = false;
        yield true;
    }

    public function getSessionId()
    {
        yield $this->session_id;
    }

    public function writeBack() {
        if ($this->isChanged) {
            yield KV::set($this->config['kv'], self::SESSION_PREFIX . $this->session_id, serialize($this->session_map), $this->ttl);
        }
    }
}