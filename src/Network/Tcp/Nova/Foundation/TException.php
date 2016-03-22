<?php
/**
 * Abs TException
 * User: moyo
 * Date: 11/4/15
 * Time: 4:45 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Foundationl;

use Kdt\Iron\Nova\Foundation\Traits\StructSpecManager;
use Exception as SysException;

abstract class TException extends SysException
{
    /**
     * Spec mgr
     */
    use StructSpecManager;

    /**
     * TException constructor.
     */
    public function __construct()
    {
        $this->staticSpecInjecting();
    }
}