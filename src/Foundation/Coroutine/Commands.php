<?php
namespace Zan\Framework\Foundation\Coroutine;

use Zan\Framework\Utilities\Types\Dir;

class Commands
{
    public static function load()
    {
        $dir = __DIR__ . '/Command/';
        $files = Dir::glob($dir, '*.php');

        if (!$files) return false;

        foreach($files as $file){
            require_once($file);
        }
    }
}






