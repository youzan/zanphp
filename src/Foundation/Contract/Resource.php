<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/14
 * Time: 21:56
 */

namespace Zan\Framework\Foundation\Contract;

interface Resource {
    public function register(PooledObject $obj);
    public function release(PooledObject $obj);
}