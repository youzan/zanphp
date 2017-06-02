<?php
namespace Zan\Framework\Utilities\DesignPattern;

trait Instance
{
    public static function newInstance() {
        return new static();
    }
}
