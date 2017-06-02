<?php

namespace Zan\Framework\Nova\Protocol\Container;

use Zan\Framework\Nova\Foundation\Traits\InstanceManager;

class Output
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

    /**
     * @return array
     */
    public function export()
    {
        $export = [];
        foreach ($this->_TSPEC as $spec)
        {
            if (property_exists($this, $spec['var']))
            {
                $export[$spec['var']] = $this->$spec['var'];
            }
        }
        return $export;
    }
}