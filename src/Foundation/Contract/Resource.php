<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/14
 * Time: 21:56
 */

namespace Zan\Framework\Foundation\Contract;

interface Resource {
    const AUTO_RELEASE = 1;
    const RLEASE_TO_POOL = 2;
    const RLEASE_AND_DESTROY = 3;
    public function release($stradegy=Resource::AUTO_RELEASE);
}