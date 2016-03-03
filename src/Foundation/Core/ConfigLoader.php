<?php
namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Utilities\Types\Dir;

class ConfigLoader
{
    use Singleton;

    //TODO: file load order
    public function load($path)
    {
        if(!is_dir($path)){
            throw new InvalidArgument('Invalid path for ConfigLoader');
        }

        $path = Dir::formatPath($path);
        $files = Dir::glob($path, '*.php');
        $fileMap = $this->parseFilesToMap($files, $path);
    }

    private function parseFilesToMap($files, $path)
    {

    }

    private function formateFilePath($file, $path, $suffix='.php')
    {

    }
}