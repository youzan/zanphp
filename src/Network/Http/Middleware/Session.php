<?php

namespace Zan\Framework\Network\Http\Middleware;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Store\Facade\Cache;
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
    private $isChanged = false;

    public function __construct(Request $request, $cookie)
    {
        $this->config = Config::get(self::CONFIG_KEY);

        $this->request = $request;
        $this->cookie = $cookie;

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

        $session = (yield Cache::get($this->config['store_key'], [$this->session_id]));
        if ($session) {
            $this->session_map = $this->sessionDecode($session);
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

    public function destroy()
    {
        $ret = (yield Cache::del($this->config['store_key'], [$this->session_id]));
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
            yield Cache::set($this->config['store_key'], [$this->session_id], $this->sessionEncode($this->session_map));
        }
    }


    private static function sessionDecode($session) {
        $sessionTable = array();
        $offset = 0;
        while ($offset < strlen($session)) {
            if (!strstr(substr($session, $offset), "|")) {
                throw new InvalidArgumentException("Invalid data, remaining: " . substr($session, $offset));
            }
            $pos = strpos($session, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session, $offset));
            $sessionTable[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $sessionTable;
    }


    public static function sessionEncode( array $data ) {
        $ret = '';
        foreach ( $data as $key => $value ) {
            if ( strcmp( $key, intval( $key ) ) === 0 ) {
                throw new InvalidArgumentException( "Ignoring unsupported integer key \"$key\"" );
            }
            if ( strcspn( $key, '|!' ) !== strlen( $key ) ) {
                throw new InvalidArgumentException( "Serialization failed: Key with unsupported characters \"$key\"" );
            }
            $v = serialize( $value );
            if ( $v === null ) {
                return null;
            }
            $ret .= "$key|$v";
        }
        return $ret;
    }
}