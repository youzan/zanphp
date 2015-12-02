<?php

namespace Zan\Framework\Utilities\Types;

class Dir
{
    public static function glob($path, $pattern=null)
    {
        if(!is_dir($path) || !$pattern) {
            return [];
        }


    }

    public static function scan($path, $recursive=true, $excludeDir=true)
    {
        $path = self::formatPath($path);
        $dh = opendir($path);
        if(!$dh) return [];

        $files = [];
        while( false !== ($file=readdir($dh)) ) {
            if($file == '.' || $file == '..'){
                continue;
            }

            $fileType = filetype($path . $file);
            if('file' == $fileType) {
                $files[] = $file;
            }

            if('dir' == $fileType) {
                if(true === $recursive) {
                    $innerFiles = Dir::scan($path . $file, $excludeDir, $recursive);
                    $files = Arr::join($files, $innerFiles);
                }

                if(false === $excludeDir) {
                    $files[] = $file;
                }
            }
        }

        closedir($dh);
        return $files;
    }

    public static function formatPath($path) {
        if('/' == substr($path,-1) ) {
            return $path;
        }

        return $path . '/';
    }
}