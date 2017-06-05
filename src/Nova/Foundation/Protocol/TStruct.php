<?php

namespace Kdt\Iron\Nova\Foundation\Protocol;

use Kdt\Iron\Nova\Foundation\Traits\StructSpecManager;

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