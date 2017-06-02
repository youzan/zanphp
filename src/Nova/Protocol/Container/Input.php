<?php

namespace Zan\Framework\Nova\Protocol\Container;

use Zan\Framework\Nova\Foundation\Traits\InstanceManager;

class Input
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var array
     */
    public $_TSPEC = [];

    /**
     * @param $TSPEC
     */
    public function setTSPEC($TSPEC)
    {
        $this->_TSPEC = $TSPEC;
    }
}