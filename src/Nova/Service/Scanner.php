<?php

namespace Kdt\Iron\Nova\Service;

use Kdt\Iron\Nova\Exception\FrameworkException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Foundation\TSpecification;
use Kdt\Iron\Nova\Utils\Dir;

class Scanner
{
    use InstanceManager;

    public function scan()
    {
        $config = NovaConfig::getInstance()->getConfig();
        foreach ($config as $item) {
            $this->scanSpec($item["appName"], $item["domain"], $item["path"], $item["namespace"]);
        }
    }

    private function scanSpec($appName, $domain, $path, $baseNamespace)
    {
        /* @var $classMap ClassMap */
        $classMap = ClassMap::getInstance();
        /* @var $registry Registry */
        $registry = Registry::getInstance();

        $map = $this->scanSpecObjects($path, $baseNamespace);
        foreach ($map as $className => $class) {
            $classMap->setSpec($className,$class);
            $registry->register(Registry::PROTO_NOVA, $domain, $appName, $class);
        }
    }

    /**
     * @param string $path
     * @param string $baseNamespace
     * @return TSpecification[]
     */
    public function scanSpecObjects($path, $baseNamespace)
    {
        $pattern = '/servicespecification/';
        $files = Dir::glob($path, $pattern);

        $map = [];
        foreach($files as $file){
            $className = $this->getClassNameFromPath($file,$path,'.php');
            $className = $baseNamespace . $className;
            $object = new $className();
            $map[$className] = $object;
        }
        return $map;
    }

    private function getClassNameFromPath($path, $prefix, $suffix)
    {
        $strPos = strlen($prefix);
        $endPos = -1 * strlen($suffix);
        $strlen = strlen($path);
        if($strlen < $strPos) {
            throw new FrameworkException('get spec className from path failed');
        }
        if($strlen < -1 * ($endPos)) {
            throw new FrameworkException('get spec className from path failed');
        }

        $className = substr($path, $strPos, $endPos);
        $className = str_replace('/', '\\', $className);

        return $className;
    }

}