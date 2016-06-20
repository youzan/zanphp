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

    public function getLockGetServices($module)
    {
        return apcu_fetch($this->getLockGetServicesKey($module));
    }

    public function lockGetServices($module)
    {
        return apcu_cas($this->getLockGetServicesKey($module), 0, 1);
    }

    public function resetLockGetServices($module)
    {
        return apcu_store($this->getLockGetServicesKey($module), 0);
    }

    public function getLockGetServicesKey($module)
    {
        return 'server_get_lock_' . $module;
    }

    public function getServices($module)
    {
        return $this->get($this->getServicesKey($module));
    }

    public function setServices($module, $servers)
    {
        return $this->set($this->getServicesKey($module), $servers);
    }

    public function getServicesKey($module)
    {
        return 'server_list_' . $module;
    }

    public function getDoWatchLastTime($module)
    {
        return $this->get($this->getSetDoWatchLastTimeKey($module));
    }

    public function setDoWatchLastTime($module)
    {
        return $this->set($this->getSetDoWatchLastTimeKey($module), time());
    }

    public function getSetDoWatchLastTimeKey($module)
    {
        return 'server_watch_last_time_' . $module;
    }

    public function lockWatch($module)
    {
        return apcu_cas($this->getLockWatchKey($module), 0, 1);
    }

    public function resetLockWatch($module)
    {
        return apcu_store($this->getLockWatchKey($module), 0);
    }

    public function getLockWatchKey($module)
    {
        return 'server_lock_watch_' . $module;
    }
}