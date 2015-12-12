<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/13
 * Time: 01:35
 */

namespace Zan\Framework\Network\Server;


class Registry implements \Zan\Framework\Utilities\DesignPattern\Registry {
    private $data = [];

    public function __construct()
    {
        $this->data = [];
    }

    public function get($key, $default=null)
    {

    }

    public function set($key, $value)
    {

    }
}