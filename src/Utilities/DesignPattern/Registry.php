<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/13
 * Time: 01:35
 */

namespace Zan\Framework\Utilities\DesignPattern;


interface Registry {
    public function get($key, $default=null);
    public function set($key, $value);
}