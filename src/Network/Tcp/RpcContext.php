<?php

namespace Zan\Framework\Network\Tcp;


use Zan\Framework\Utilities\DesignPattern\Context;

class RpcContext
{
    const MAX_NOVA_ATTACH_LEN = 30000; // nova header 总长度 0x7fff;

    const KEY = "__rpc_ctx";

    private $ctx = [];

    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->ctx;
        } else {
            return isset($this->ctx[$key]) ? $this->ctx[$key] : $default;
        }
    }

    public function set($key, $value)
    {
        $old = null;

        if (isset($this->ctx[$key])) {
            $old = $this->ctx[$key];
        }

        if ($value === null) {
            unset($this->ctx[$key]);
        } else {
            $this->ctx[$key] = $value;
        }

        return $old;
    }

    public function bindTaskCtx(Context $taskCtx)
    {
        foreach ($this->ctx as $k => $v) {
            $taskCtx->set($k, $v);
        };
        $taskCtx->set(static::KEY, $this);
    }

    public static function unpack($novaAttach)
    {
        $self = new static;

        $ctx = json_decode($novaAttach, true, 512, JSON_BIGINT_AS_STRING);
        if (is_array($ctx)) {
            $self->ctx = $ctx;
        } else {
            $self->ctx = [];
        }

        return $self;
    }

    public function pack()
    {
        $ctx = $this->ctx;
        if ($ctx === []) {
            $ctx = new \stdClass();
        }

        $raw = json_encode($ctx, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (strlen($raw) >= self::MAX_NOVA_ATTACH_LEN) {
            return '{"error":"len of attach overflow"}';
        } else {
            return $raw;
        }
    }
}