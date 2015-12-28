<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/11
 * Time: 13:47
 */

namespace Zan\Framework\Foundation\Contract;


interface PooledObjectFactory
{
    public function create(); /* PooledObject */
    public function destroy(PooledObject $object);
}