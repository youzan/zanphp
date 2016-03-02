<?php
namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Utilities\Types\Dir;

class ConfigLoader
{
    use Singleton;
    public function load($path)
    {
        if(!is_dir($path)){
            throw new InvalidArgument('Invalid path for ConfigLoader');
        }

    }

}