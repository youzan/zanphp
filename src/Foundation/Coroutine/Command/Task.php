<?php

use Zan\Framework\Foundation\Coroutine\Signal;
use Zan\Framework\Foundation\Coroutine\SysCall;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Http\Cookie;
use Zan\Framework\Network\Tcp\RpcContext;



function getRpcContext($key = null, $default = null)
{
    return new SysCall(function (Task $task) use($key, $default) {
        $context = $task->getContext();
        $rpcCtx = $context->get("rpc-context", null, RpcContext::class);
        if ($rpcCtx) {
            $task->send($rpcCtx->get($key, $default));
        } else {
            $task->send($default);
        }

        return Signal::TASK_CONTINUE;
    });
}

function setRpcContext($key, $value)
{
    return new SysCall(function (Task $task) use ($key, $value) {
        $context = $task->getContext();
        $rpcCtx = $context->get("rpc-context", null, RpcContext::class);
        if ($rpcCtx === null) {
            $rpcCtx = new RpcContext;
            $context->set("rpc-context", $rpcCtx);
        }
        $task->send($rpcCtx->set($key, $value));
        return Signal::TASK_CONTINUE;
    });
}

function getCookieHandler()
{
    return new SysCall(function (Task $task) {
        $context = $task->getContext();
        $cookie = $context->get('cookie');
        $task->send($cookie);

        return Signal::TASK_CONTINUE;
    });
}

function cookieGet($key, $default = null)
{
    return new SysCall(function (Task $task) use ($key, $default) {
        $context = $task->getContext();
        $request = $context->get('request');
        $cookies = $request->cookies;
        $value = isset($key) ? $cookies->get($key, $default) : null;
        $task->send($value);

        return Signal::TASK_CONTINUE;
    });
}

function cookieSet($key, $value = null, $expire = 0, $path = null, $domain = null, $secure = null, $httpOnly = null)
{
    $args = func_get_args();
    return new SysCall(function (Task $task) use ($args) {
        $context = $task->getContext();
        /** @var Cookie $cookie */
        $cookie = $context->get('cookie');
        $ret = $cookie->set(...$args);
        $task->send($ret);

        return Signal::TASK_CONTINUE;
    });
}

function getSessionHandler()
{
    return new SysCall(function (Task $task) {
        $context = $task->getContext();
        $session = $context->get('session');
        $value = $session ? $session : null;
        $task->send($value);
        return Signal::TASK_CONTINUE;
    });
}

function getServerHandler()
{
    return new SysCall(function (Task $task) {
        $context = $task->getContext();
        $request = $context->get('request');
        $value = $request ? $request->server : null;
        $task->send($value);
        return Signal::TASK_CONTINUE;
    });
}