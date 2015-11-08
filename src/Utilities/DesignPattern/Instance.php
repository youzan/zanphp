<?php
namespace Zan\Framework\Utilities\DesignPattern;

trait Instance {
    public static function newInstance() {
        return self::instance(); 
    }

    public static function instance() {
        return new self();    
    }
}
