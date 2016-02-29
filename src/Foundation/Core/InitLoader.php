<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/2/26
 * Time: 22:30
 */

namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Utilities\Types\Dir;

class InitLoader {
    use Singleton;

    private $orderFile = '/.order';

    public function load($path)
    {
        if(!$path){
            return [];
        }

        if(!is_dir($path)){
            return [];
        }

        return $this->doLoading($path);
    }

    public function doLoading($path)
    {
        $classes = [];

        $path = Dir::formatPath($path);
        $files = Dir::glob($path, '*.php', false);



        return $classes;
    }

}