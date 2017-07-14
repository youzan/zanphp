<?php

namespace Zan\Framework\Foundation\Core;


use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Utilities\Types\Dir;

class Loader
{
    use Singleton;

    public function load($path)
    {
        if(!is_dir($path)){
            throw new InvalidArgumentException('Invalid path for Loader:' . $path);
        }

        $path = Dir::formatPath($path);
        $files = Dir::glob($path, '^[a-zA-Z]*.php', Dir::SCAN_BFS);

        foreach ($files as $file) {
            include_once $file;
        }
    }
}