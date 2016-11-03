<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/9
 * Time: 14:24
 */
namespace Zan\Framework\Foundation\Coroutine;

//use Zan\Framework\Foundation\Core\ClassLoader;
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






