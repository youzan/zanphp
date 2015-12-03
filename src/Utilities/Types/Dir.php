<?php

namespace Zan\Framework\Utilities\Types;

class Dir
{
    public static function glob($path, $pattern=null)
    {
        if(!is_dir($path) || !$pattern) {
            return [];
        }

        $files = Dir::scan($path);
        $result = [];
        foreach($files as $file) {
            if(false === self::matchPattern($pattern, $file) ) {
                continue;
            }
            $result[] = $file;
        }
        return $result;
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
                $files[] = $path . $file;
            }

            if('dir' == $fileType) {
                if(true === $recursive) {
                    $innerFiles = Dir::scan($path . $file . '/', $recursive, $excludeDir);
                    $files = Arr::join($files, $innerFiles);
                }

                if(false === $excludeDir) {
                    $files[] = $path . $file . '/';
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

    public static function matchPattern($pattern, $file)
    {
        $replaceMap = [
            '*'     => '.*',
            '.'     => '\.',
            '+'     => '.+',
        ];

        $pattern = str_replace(array_keys($replaceMap), array_values($replaceMap), $pattern);
        $pattern = '/' . $pattern . '/i';

        if(preg_match($pattern, $file)) {
            return true;
        }

        return false;
    }
}