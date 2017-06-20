<?php

namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Foundation\Core\Config;
use Kdt\Iron\Nova\Nova;
use Zan\Framework\Network\Common\Curl;

class ServiceUnregister
{
    public function unRegister()
    {
        $config = Config::get('registry');
        if (empty($config)) {
            return;
        }

        $isRegistered = ServerRegisterInitiator::getInstance()->getRegister();
        if ($isRegistered == ServerRegisterInitiator::DISABLE_REGISTER) {
            return;
        }

        $keys = Nova::getEtcdKeyList();
        foreach ($keys as list($protocol, $domain, $appName)) {
            $this->doUnRegisterOneGroup($protocol, $domain, $appName);
        }
    }

    private function doUnRegisterOneGroup($protocol, $domain, $appName)
    {
        $config = [];
        $config["services"] = Nova::getAvailableService($protocol, $domain, $appName);
        $config["domain"] = $domain;
        $config["appName"] = $appName;
        $config["protocol"] = $protocol;

        $type = Config::get("haunt.type", "etcd");
        if ($type === "etcd") {
            $this->toUnRegisterToEtcdV2($config);
        } else if ($type === "haunt") {
            $this->toUnRegisterByHaunt($config);
        }
    }

    private function toUnRegisterToEtcdV2($config)
    {
        // NOTICE! 这里不能删除节点数据，cloud监控平台仍要使用，将状态重置为unreg, 将ttl更新为0，
        $node = ServerRegister::getRandEtcdNode();

        list($etcdV2Key, $etcdV2Value) = ServerRegister::createEtcdV2KV($config, ServerDiscovery::SRV_STATUS_UNREG);
        $url = "http://{$node["host"]}:{$node["port"]}/v2/keys/$etcdV2Key";
        $curl = new Curl();

        $resp = $curl->request(Curl::METHOD_PUT, $url, [
            "value" => json_encode($etcdV2Value),
            "ttl" => 0,
        ]);

        $code = $resp->statusCode();
        sys_echo("unregister [statusCode=$code," . $this->inspect($etcdV2Value) . "]");
    }

    private function toUnRegisterByHaunt($config)
    {
        $haunt = Config::get('registry.haunt');
        $url = 'http://'.$haunt['unregister']['host'].':'.$haunt['unregister']['port'].$haunt['unregister']['uri'];
        $curl = new Curl();
        $body = ServerRegister::createHauntBody($config);
        sys_echo("unregister " . $this->inspect($body['SrvList'][0]));
        $unregister = $curl->post($url, $body);
        sys_echo($unregister);
    }

    private function inspect($config)
    {
        $map = [];
        foreach ($config as $k => $v) {
            if ($k === "ExtData") {
                continue;
            }
            $map[] = "$k=$v";
        }
        return implode(", ", $map);
    }
}