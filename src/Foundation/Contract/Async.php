<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/10/23
 * Time: 13:57
 */

namespace Zan\Framework\Foundation\Contract;


interface Async
{
    public function execute(callable $callback);
}