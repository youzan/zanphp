<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/5/7
 * Time: 00:43
 */

namespace Zan\Framework\Test\Foundation\Container\Stub;


class Singleton
{
    private $uid;

    public function __construct($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }
}