<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/13
 * Time: 01:35
 */

namespace Zan\Framework\Utilities\DesignPattern;


interface Registry {
    public function get();
    public function set();
}