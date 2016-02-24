<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/11
 * Time: 13:47
 */

namespace Zan\Framework\Foundation\Contract;


abstract class PooledObject
{
    public function isAlive() /* bool */
    {
        return true;
    }
}