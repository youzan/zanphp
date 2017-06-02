<?php

namespace Zan\Framework\Foundation\Contract;


abstract class PooledObject
{
    public function isAlive() /* bool */
    {
        return true;
    }
}