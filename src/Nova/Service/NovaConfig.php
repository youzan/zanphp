<?php

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Exception\FrameworkException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Zan\Framework\Foundation\Core\Path;

class NovaConfig
{
    use InstanceManager;

    private static $genericInvokePath = "vendor/zanphp/zan/src/Nova/Generic/gen-php";

    private static $genericInvokeBaseNamespace = "Com\\Youzan\\Nova\\";

    private static $required = ["protocol", "domain", "appName", "path", "namespace"];

    private $config = [];

    private $etcdNamespaces = [];

    public function setConfig(array $config)
    {
        self::validatorConfig($config);

        $etcdKeys = []; // 按注册分组
        foreach ($config as &$item) {
            $app = $item["appName"];
            $domain = $item["domain"];
            $proto = $item["protocol"];
            $namespace = $item["namespace"];

            $etcdKey = Registry::buildEtcdKey($proto, $domain, $app);
            $etcdKeys[$etcdKey] = [$proto, $domain, $app];
            $item["path"] = realpath($item["path"]) . '/';

            $nsKey = $this->buildNamespaceKey($proto, $app);
            if (isset($this->etcdNamespaces[$nsKey])) {
                $oldNamespace = $this->etcdNamespaces[$nsKey];
                if ($oldNamespace !== $namespace) {
                    throw new FrameworkException("the same namespace must be defined in the one same app");
                }
            }
            $this->etcdNamespaces[$nsKey] = $namespace;
        }
        unset($item);

        // 按注册分组添加 泛化调用服务
        foreach ($etcdKeys as list($proto, $domain, $app)) {
            if ($proto === Registry::PROTO_NOVA) {
                $config[] = [
                    "appName" => $app,
                    "domain" => $domain,
                    "path"  => Path::getRootPath() . self::$genericInvokePath . "/",
                    "namespace" => self::$genericInvokeBaseNamespace,
                    "protocol" => Registry::PROTO_NOVA,
                ];
            }
        }

        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    private static function validatorConfig(array $config)
    {
        foreach ($config as $item) {
            foreach (self::$required as $filed) {
                if (!isset($item[$filed])) {
                    throw new FrameworkException("nova $filed not defined");
                }
            }
        }
    }

    public function removeNovaNamespace($proto, $domain, $appName, $serviceName)
    {
        $etcdKey = $this->buildNamespaceKey($proto, $appName);
        if (isset($this->etcdNamespaces[$etcdKey])) {
            return substr($serviceName, strlen($this->etcdNamespaces[$etcdKey]));
        } else {
            throw new FrameworkException("can not find config: proto=$proto, domain=$domain, appName=$appName, service=$serviceName");
        }
    }

    private function buildNamespaceKey($proto, $app)
    {
        // nova协议header中移除domain, 除非使用attachment传递,
        // 否则不知道客户端请求哪个domain的服务, 这里不使用domain
        return "$proto:$app";
    }
}