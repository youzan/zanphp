<?php
namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Utilities\Types\Dir;
use Zan\Framework\Utilities\Types\Arr;

class ConfigLoader
{
    use Singleton;

    public function load($path)
    {
        if(!is_dir($path)){
            throw new InvalidArgument('Invalid path for ConfigLoader');
        }
        $configFiles = Dir::glob($path,'*.php',Dir::SCAN_BFS);
        $configMap = [];
        foreach($configFiles as $configFile){
            $newConfigs = require $configFile;
            if(!is_array($newConfigs)){
                throw new InvalidArgument("config set error:".$configFile);
            }

            $relativePath = substr($configFile,strlen($path), -4);
            $relativePath = trim($relativePath,'/');
            $newConfigMap = Arr::createMap(explode('/',$relativePath),$newConfigs);

            $configMap = Arr::merge($configMap,$newConfigMap);
        }
        return $configMap;
    }

}