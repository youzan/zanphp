<?php
/**
 * Abs TStruct
 * User: moyo
 * Date: 9/10/15
 * Time: 4:35 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Foundation\Protocol;

use Zan\Framework\Network\Tcp\Nova\Foundation\Traits\StructSpecManager;

abstract class TStruct
{
    /**
     * Spec mgr
     */
    use StructSpecManager;

    /**
     * TStruct constructor.
     */
    public function __construct()
    {
        $this->staticSpecInjecting();
    }
}