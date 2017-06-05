<?php

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Exception\FrameworkException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Foundation\TSpecification;

class Registry
{
    use InstanceManager;

    const PROTO_NOVA = "nova";

    private $etcdKeyList = [];
    private $map = [];

    public static function buildEtcdKey($protocol, $domain, $appName)
    {
        return "$protocol:$domain:$appName";
    }

    public function register($protocol, $domain, $appName, TSpecification $class)
    {
        $etcdKey = self::buildEtcdKey($protocol, $domain, $appName);

        if (!isset($this->map[$etcdKey])) {
            $this->etcdKeyList[$etcdKey] = [$protocol, $domain, $appName];
            $this->map[$etcdKey] = [];
        }

        $serviceName = $class->getServiceName();
        if(isset($this->map[$etcdKey][$serviceName])){
            throw new FrameworkException("duplicated implement of :$serviceName in app $appName");
        }
        $methods = $class->getServiceMethods();
        $this->map[$etcdKey][$serviceName] = $methods;
    }

    public function getEtcdKeyList()
    {
        return $this->etcdKeyList;
    }

    public function getAll($protocol, $domain, $appName)
    {
        $etcdKey = self::buildEtcdKey($protocol, $domain, $appName);

        if(empty($this->map)) {
            return [];
        }

        if (!isset($this->map[$etcdKey])) {
            throw new FrameworkException("etcd key $etcdKey not found");
        }

        $ret = [];
        foreach($this->map[$etcdKey] as $serviceName => $methods) {
            $ret[] = [
                'service' => $this->formatServiceName($serviceName),
                'methods' => $methods,
            ];
        }

        return $ret;
    }

    private function formatServiceName($serviceName)
    {
        $serviceArr = explode('.',$serviceName);
        $className = array_pop($serviceArr);

        $serviceArr = array_map('lcfirst',$serviceArr);
        $serviceArr[] = $className;

        return join('.', $serviceArr);
    }
}