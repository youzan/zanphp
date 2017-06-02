<?php

namespace Zan\Framework\Foundation\Contract;


interface Async
{
    public function execute(callable $callback, $task);
}