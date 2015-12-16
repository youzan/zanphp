<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 23:57
 */

namespace Zan\Framework\Foundation\View\Plugin;


class KeyHash {
    private $counter = 1;
    public function __construct()
    {
        $this->counter = 1;
    }

    public function hash($key)
    {

    }
}