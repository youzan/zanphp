<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/19
 * Time: 下午1:55
 */

namespace Zan\Framework\Foundation\Core;


use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Utilities\Types\Dir;

class Loader
{
    use Singleton;

    public function load($path)
    {
        if(!is_dir($path)){
            throw new InvalidArgumentException('Invalid path for Loader');
        }

        $path = Dir::formatPath($path);
        $files = Dir::glob($path, '^[a-zA-Z]*.php', Dir::SCAN_BFS);

        foreach ($files as $file) {
            include_once $file;
        }
    }
}