<?php
namespace Kdt\Iron\Nova;

use Kdt\Iron\Nova\Protocol\Packer;
use Kdt\Iron\Nova\Service\Initator;
use Kdt\Iron\Nova\Service\Registry;
use Kdt\Iron\Nova\Service\NovaConfig;
use Kdt\Iron\Nova\Service\PackerFacade;
use Kdt\Iron\Nova\Service\Scanner;

class Nova
{

    const CLIENT = Packer::CLIENT;
    const SERVER = Packer::SERVER;

    public static function init(array $config)
    {
        Initator::newInstance()->init($config);
    }

    public static function getEtcdKeyList()
    {
        /** @var $registry Registry */
        $registry = Registry::getInstance();
        return $registry->getEtcdKeyList();
    }

    /**
     * @param $path
     * @param $baseNamespace
     * @return Foundation\TSpecification[]
     */
    public static function getSpec($path, $baseNamespace)
    {
        /** @var Scanner $scanner */
        $scanner = Scanner::getInstance();
        return $scanner->scanSpecObjects($path, $baseNamespace);
    }

    public static function getAvailableService($protocol, $domain, $appName)
    {
        /** @var $registry Registry */
        $registry = Registry::getInstance();
        return $registry->getAll($protocol, $domain, $appName);
    }

    public static function removeNovaNamespace($serviceName, $appName)
    {
        /* @var $novaConfig NovaConfig */
        $novaConfig = NovaConfig::getInstance();
        return $novaConfig->removeNovaNamespace("nova", null, $appName, $serviceName);
    }

    public static function decodeServiceArgs($serviceName, $methodName, $binArgs, $side = self::SERVER)
    {
        /* @var $packer PackerFacade */
        $packer = PackerFacade::getInstance();
        return $packer->decodeServiceArgs($serviceName, $methodName, $binArgs, $side);
    }

    public static function encodeServiceOutput($serviceName, $methodName, $output, $side = self::SERVER)
    {
        /* @var $packer PackerFacade */
        $packer = PackerFacade::getInstance();
        return $packer->encodeServiceOutput($serviceName, $methodName, $output, $side);
    }

    public static function encodeServiceException($serviceName, $methodName, $exception, $side = self::SERVER)
    {
        /* @var $packer PackerFacade */
        $packer = PackerFacade::getInstance();
        return $packer->encodeServiceException($serviceName, $methodName, $exception, $side);
    }

}