<?php

/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/9
 * Time: 14:24
 */
use Zan\Framework\Foundation\Coroutine\SysCall;
use Zan\Framework\Foundation\Coroutine\Task;
use \Zan\Framework\Foundation\Coroutine\Signal;

function taskSleep() {
    return new SysCall(function(Task $task) {

    });
}

function taskAwake(){
    return new SysCall(function(Task $task) {

    });
}

function newTask(\Generator $gen=null) {
    return new SysCall(function(Task $task) use ($gen) {

    });
}

function killTask() {
    return new SysCall(function(Task $task)  {

    });
}

function getTaskId() {
    return new SysCall(function(Task $task)  {
        $coroutine = $task->getCoroutine();
        $coroutine->send($task->getTaskId());

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








