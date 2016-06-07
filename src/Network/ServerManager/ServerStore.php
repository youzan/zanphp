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

    public function getLockGetServices($modules)
    {
        return apcu_fetch($this->getLockGetServicesKey($modules));
    }

    public function lockGetServices($modules)
    {
        return apcu_cas($this->getLockGetServicesKey($modules), 0, 1);
    }

    public function resetLockGetServices($modules)
    {
        return apcu_store($this->getLockGetServicesKey($modules), 0);
    }

    public function getLockGetServicesKey($modules)
    {
        return 'server_get_lock_' . $modules;
    }

    public function getServices($modules)
    {
        return $this->get($this->getServicesKey($modules));
    }

    public function setServices($modules, $servers)
    {
        return $this->set($this->getServicesKey($modules), $servers);
    }

    public function getServicesKey($modules)
    {
        return 'server_list_' . $modules;
    }

    public function getDoWatchLastTime($modules)
    {
        return $this->get($this->getSetDoWatchLastTimeKey($modules));
    }

    public function setDoWatchLastTime($modules)
    {
        return $this->set($this->getSetDoWatchLastTimeKey($modules), time());
    }

    public function getSetDoWatchLastTimeKey($modules)
    {
        return 'server_watch_last_time_' . $modules;
    }

    public function lockWatch($modules)
    {
        return apcu_cas($this->getLockWatchKey($modules), 0, 1);
    }

    public function resetLockWatch($modules)
    {
        return apcu_store($this->getLockWatchKey($modules), 0);
    }

    public function getLockWatchKey($modules)
    {
        return 'server_lock_watch_' . $modules;
    }
}