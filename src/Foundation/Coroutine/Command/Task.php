<?php

use Zan\Framework\Foundation\Contract\Resource;
use Zan\Framework\Foundation\Coroutine\Parallel;
use Zan\Framework\Foundation\Coroutine\Signal;
use Zan\Framework\Foundation\Coroutine\SysCall;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Http\Cookie;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Tcp\RpcContext;

function taskSleep($ms)
{
    return new SysCall(function (Task $task) use ($ms) {
        Timer::after($ms, function () use ($task) {
            $task->send(null);
            $task->run();
        });

        return Signal::TASK_SLEEP;
    });
}

function newTask(\Generator $gen = null)
{
    return new SysCall(function (Task $task) use ($gen) {
        $context = $task->getContext();
        Task::execute($gen, $context, 0, $task);

        $task->send(null);
        return Signal::TASK_CONTINUE;
    });
}

function go(\Generator $coroutine)
{
    return newTask($coroutine);
}

function defer(callable $callback)
{

}

function deferRelease(Resource $res, $stradegy = Resource::AUTO_RELEASE)
{
}

function release(Resource $res, $stradegy = Resource::AUTO_RELEASE)
{

}

function killTask()
{
    return new SysCall(function (Task $task) {
        return Signal::TASK_KILLED;
    });
}

function getTaskId()
{
    return new SysCall(function (Task $task) {
        $task->send($task->getTaskId());

        return Signal::TASK_CONTINUE;
    });
}

function getRpcContext($key = null, $default = null)
{
    return new SysCall(function (Task $task) use($key, $default) {
        $context = $task->getContext();
        $rpcCtx = $context->get(RpcContext::KEY, null, RpcContext::class);
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
        $rpcCtx = $context->get(RpcContext::KEY, null, RpcContext::class);
        if ($rpcCtx === null) {
            $rpcCtx = new RpcContext;
            $context->set(RpcContext::KEY, $rpcCtx);
        }
        $task->send($rpcCtx->set($key, $value));
        return Signal::TASK_CONTINUE;
    });
}

function getContext($key, $default = null)
{
    return new SysCall(function (Task $task) use ($key, $default) {
        $context = $task->getContext();
        $task->send($context->get($key, $default));

        return Signal::TASK_CONTINUE;
    });
}

function setContext($key, $value)
{
    return new SysCall(function (Task $task) use ($key, $value) {
        $context = $task->getContext();
        $task->send($context->set($key, $value));

        return Signal::TASK_CONTINUE;
    });
}

function getContextObject()
{
    return new SysCall(function (Task $task) {
        $context = $task->getContext();
        $task->send($context);

        return Signal::TASK_CONTINUE;
    });
}

function getTaskResult()
{
    return new SysCall(function (Task $task) {
        $task->send($task->getSendValue());

        return Signal::TASK_CONTINUE;
    });
}

function getTaskStartTime($format = null)
{
    return new SysCall(function (Task $task) use ($format) {
    });
}

function waitFor(\Generator $coroutine)
{
    return new SysCall(function (Task $task) use ($coroutine) {

    });
}

function wait()
{
    return new SysCall(function (Task $task) {

    });
}

function parallel($coroutines)
{
    return new SysCall(function (Task $task) use ($coroutines) {
        (new Parallel($task))->call($coroutines);

        return Signal::TASK_WAIT;
    });
}

function async(callable $callback)
{
    return new SysCall(function (Task $task) use ($callback) {
        $context = $task->getContext();
        $queue = $context->get('async_task_queue', []);
        $queue[] = $callback;
        $context->set('async_task_queue', $queue);
        $task->send(null);

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