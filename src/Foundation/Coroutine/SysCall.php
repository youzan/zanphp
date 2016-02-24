<?php
namespace Zan\Framework\Foundation\Coroutine;

class SysCall
{
    protected $callback = null;

    public function __construct(\Closure $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(Task $task)
    {
        return call_user_func($this->callback, $task);
    }
}