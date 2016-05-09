<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/5/8
 * Time: 23:45
 */

namespace Zan\Framework\Testing;


class Stub
{
    private $realClassName = null;

    /**
     * @return null
     */
    public function getRealClassName()
    {
        return $this->realClassName;
    }


}