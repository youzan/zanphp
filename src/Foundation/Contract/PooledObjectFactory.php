<?php

namespace Zan\Framework\Foundation\Contract;


interface PooledObjectFactory
{
    public function create(); /* PooledObject */
    public function destroy(PooledObject $object);
}