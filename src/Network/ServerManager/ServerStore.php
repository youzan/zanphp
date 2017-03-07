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

    const LOCK_INIT = -1;

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

    private function getServiceWaitIndexKey($appName)
    {
        return 'service_wait_index_' . $appName;
    }

    public function getServiceWaitIndex($appName)
    {
        $key = $this->getServiceWaitIndexKey($appName);
        return apcu_fetch($key);
    }

    public function setServiceWaitIndex($appName, $waitIndex)
    {
        $key = $this->getServiceWaitIndexKey($appName);
        return apcu_store($key, $waitIndex);
    }

    public function getLockGetServices($appName)
    {
        return apcu_fetch($this->getLockGetServicesKey($appName));
    }

    public function lockGetServices($appName)
    {
        return apcu_cas($this->getLockGetServicesKey($appName), 0, 1);
    }

    public function resetLockGetServices($appName)
    {
        return apcu_store($this->getLockGetServicesKey($appName), 0);
    }

    public function getLockGetServicesKey($appName)
    {
        return 'server_get_lock_' . $appName;
    }

    public function getServices($appName)
    {
        return $this->get($this->getServicesKey($appName));
    }

    public function setServices($appName, $servers)
    {
        return $this->set($this->getServicesKey($appName), $servers);
    }

    public function getServicesKey($appName)
    {
        return 'server_list_' . $appName;
    }

    public function getDoWatchLastTime($appName)
    {
        return $this->get($this->getSetDoWatchLastTimeKey($appName));
    }

    public function setDoWatchLastTime($appName)
    {
        return $this->set($this->getSetDoWatchLastTimeKey($appName), time());
    }

    public function getSetDoWatchLastTimeKey($appName)
    {
        return 'server_watch_last_time_' . $appName;
    }

    public function lockWatch($appName)
    {
        return apcu_cas($this->getLockWatchKey($appName), 0, 1);
    }

    public function resetLockWatch($appName)
    {
        return apcu_store($this->getLockWatchKey($appName), 0);
    }

    public function getLockWatchKey($appName)
    {
        return 'server_lock_watch_' . $appName;
    }

    public function lockDiscovery($workerId)
    {
        return apcu_cas($this->getLockDiscoveryKey(), self::LOCK_INIT, $workerId);
    }

    public function unlockDiscovery($workerId)
    {
        // 保证只有加锁的worker才能加锁
        return apcu_cas($this->getLockDiscoveryKey(), $workerId, self::LOCK_INIT);
    }

    public function resetLockDiscovery()
    {
        return apcu_store($this->getLockDiscoveryKey(), self::LOCK_INIT);
    }

    private function getLockDiscoveryKey()
    {
        return 'sever_lock_discovery';
    }
}