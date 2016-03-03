<?php

namespace Zan\Framework\Utilities\Types;

class Dir
{

    const SCAN_CURRENT_DIR  = 'current';
    const SCAN_BFS = 'bfs';
    const SCAN_DFS = 'dfs';

    public static function glob($path, $pattern=null, $recursive=self::SCAN_DFS)
    {
        if(!is_dir($path) || !$pattern) {
            return [];
        }

        $files = Dir::scan($path, $recursive);
        $result = [];
        foreach($files as $file) {
            if(false === self::matchPattern($pattern, $file) ) {
                continue;
            }
            $result[] = $file;
        }
        return $result;
    }

    public static function scan($path, $recursive=self::SCAN_CURRENT_DIR, $excludeDir=true)
    {
        $files = [];
        switch($recursive){
            case self::SCAN_CURRENT_DIR:
                $files = self::getFilesCurrent($path,$excludeDir);
                break;
            case self::SCAN_BFS:
                $files = self::getFilesBfs($path,$excludeDir);;
                break;
            case self::SCAN_DFS:
                $files = self::getFilesDfs($path,$excludeDir);
                break;
        }
        return $files;
    }

    private static function getFilesCurrent($path,$excludeDir=true){
        $path = self::formatPath($path);
        $dh = opendir($path);
        if(!$dh) return [];
        $files = [];
        while( false !== ($file=readdir($dh)) ) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $fileType = filetype($path.$file);
            if('dir' == $fileType && false === $excludeDir) {
                $files[] = $path . $file . '/';
            }
            if('file' == $fileType) {
                $files[] = $path . $file;
            }
        }
        closedir($dh);
        return $files;
    }

    private static function getFilesBfs($path,$excludeDir=true){
        $files = [];
        $queue = new \SplQueue();
        $queue->enqueue($path);
        while(!$queue->isEmpty()){
            $file = $queue->dequeue();
            $fileType = filetype($file);
            if('dir' == $fileType) {
                $subFiles = self::getFilesCurrent($file,false);
                foreach($subFiles as $subFile){
                    $queue->enqueue($subFile);
                }
                if(false === $excludeDir && $file != $path){
                    $files[] = $file ;
                }
            }
            if('file' == $fileType) {
                $files[] = $file;
            }
        }
        return $files;
    }

    private static function getFilesDfs($path,$excludeDir=true){
        $files = [];
        $subFiles = self::getFilesCurrent($path,false);
        foreach($subFiles as $subFile){
            $fileType = filetype($subFile);
            if('dir' == $fileType) {
                $innerFiles = self::getFilesDfs($subFile,$excludeDir);
                $files = Arr::join($files,$innerFiles);
                if(false === $excludeDir){
                    $files[] = $subFile;
                }
            }
            if('file' == $fileType) {
                $files[] = $subFile;
            }
        }
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

    public static function basename($pathes, $suffix='')
    {
        if(!$pathes) return [];

        $ret = [];
        foreach($pathes as $path){
            $ret[] = basename($path, $suffix);
        }

        return $ret;
    }
}