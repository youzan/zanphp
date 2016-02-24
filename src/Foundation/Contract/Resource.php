<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/14
 * Time: 21:56
 */

namespace Zan\Framework\Foundation\Contract;

interface Resource
{
    const AUTO_RELEASE = 1;
    const RELEASE_TO_POOL = 2;
    const RELEASE_AND_DESTROY = 3;

    public function release($strategy = Resource::AUTO_RELEASE);
}