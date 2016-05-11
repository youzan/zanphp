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
    const YZ_SESSION_KEY = 'KDTSESSIONID';
    const CONFIG_KEY = 'server.session';

    private $request;
    private $cookie;
    private $session_id;
    private $session_map = array();
    private $config;
    private $kv;
    private $ttl;
    private $isChanged = false;

    public function __construct(Request $request, $cookie)
    {
        $this->config = Config::get(self::CONFIG_KEY);

        $this->request = $request;
        $this->cookie = $cookie;
        $this->kv = KV::getInstance($this->config['kv']);
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
        }

        $session = (yield $this->kv->get($this->session_id));
        if ($session) {
            $this->session_map = unserialize($session);
        }
        yield true;
    }

    public function set($key, $value)
    {
        $this->session_map[$key] = $value;
        $this->isChanged = true;
        return true;
    }

    public function get($key)
    {
        return isset($this->session_map[$key]) ? $this->session_map[$key] : null;
    }

    public function writeBack() {
        if ($this->isChanged) {
            yield $this->kv->set($this->session_id, serialize($this->session_map), $this->ttl);
        }
    }
}