<?php
/**
 * Container of input (bin-acc)
 * User: moyo
 * Date: 10/23/15
 * Time: 4:02 PM
 */

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