<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/25
 * Time: 上午11:52
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class ServerStore
{
    use Singleton;

    public function set($key, $value)
    {
        if (apcu_exists($key)) {
            return apcu_store($key, json_encode($value));
        } else {
            return apcu_add($key, json_encode($value));
        }
    }

    public function get($key)
    {
        $data = apcu_fetch($key);
        if ('' != $data) {
            return json_decode($data, true);
        }
        return null;
    }

    public function getLockGetServices($serviceName)
    {
        return apcu_fetch($this->getLockGetServicesKey($serviceName));
    }

    public function lockGetServices($serviceName)
    {
        return apcu_cas($this->getLockGetServicesKey($serviceName), 0, 1);
    }

    public function resetLockGetServices($serviceName)
    {
        return apcu_store($this->getLockGetServicesKey($serviceName), 0);
    }

    public function getLockGetServicesKey($serviceName)
    {
        return 'server_get_lock_' . $serviceName;
    }

    public function getServices($serviceName)
    {
        return $this->get($this->getServicesKey($serviceName));
    }

    public function setServices($serviceName, $servers)
    {
        return $this->set($this->getServicesKey($serviceName), $servers);
    }

    public function getServicesKey($serviceName)
    {
        return 'server_list_' . $serviceName;
    }

    public function getDoWatchLastTime($serviceName)
    {
        return $this->get($this->getSetDoWatchLastTimeKey($serviceName));
    }

    public function setDoWatchLastTime($serviceName)
    {
        return $this->set($this->getSetDoWatchLastTimeKey($serviceName), time());
    }

    public function getSetDoWatchLastTimeKey($serviceName)
    {
        return 'server_watch_last_time_' . $serviceName;
    }

    public function lockWatch($serviceName)
    {
        return apcu_cas($this->getLockWatchKey($serviceName), 0, 1);
    }

    public function resetLockWatch($serviceName)
    {
        return apcu_store($this->getLockWatchKey($serviceName), 0);
    }

    public function getLockWatchKey($serviceName)
    {
        return 'server_lock_watch_' . $serviceName;
    }
}