<?php

/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/9
 * Time: 14:24
 */
use Zan\Framework\Foundation\Coroutine\SysCall;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Coroutine\Signal;
use Zan\Framework\Foundation\Contract\Resource;

function taskSleep() {
    return new SysCall(function(Task $task) {
        $task->send(null);

        return Signal::TASK_SLEEP;
    });
}

function newTask(\Generator $gen=null) {
    return new SysCall(function(Task $task) use ($gen) {
        $task->send(null);

        return Signal::TASK_CONTINUE;
    });
}

function killTask() {
    return new SysCall(function(Task $task)  {
        return Signal::TASK_KILLED;
    });
}

function getTaskId() {
    return new SysCall(function(Task $task)  {
        $task->send($task->getTaskId());

        return Signal::TASK_CONTINUE;
    });
}

function getTaskResult() {
    return new SysCall(function(Task $task)  {
        $task->send($task->getSendValue());

        return Signal::TASK_CONTINUE;
    });
}

function getTaskStartTime($format=null) {
    return new SysCall(function(Task $task) use ($format) {
    });
}

function waitFor(\Generator $coroutine) {
    return new SysCall(function(Task $task) use ($coroutine) {

    });
}

function wait() {
    return new SysCall(function(Task $task) {

    });
}

function defer(callable $callback)
{

}

function deferRelease(Resource $res, $stradegy=Resource::AUTO_RELEASE)
{

}

function release(Resource $res, $stradegy=Resource::AUTO_RELEASE)
{

}










